<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Support\Arabic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class LeadIntakeController extends Controller
{
    /**
     * The landing-page form's only endpoint.
     *
     * Returns JSON in every case (including validation failure) because the Vue
     * form owns the error rendering — a redirect here would blank the page the
     * visitor filled in.
     */
    public function store(Request $request): JsonResponse
    {
        // Honeypot: a real person never fills a field they cannot see. Answer
        // 200 so the bot has nothing to learn from the response.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true, 'message' => 'تمام، وصلنا طلبك.']);
        }

        $request->merge([
            'phone' => Arabic::digits((string) $request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'business_type' => ['nullable', 'string', 'max:60'],
            'phone' => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'governorate' => ['nullable', 'string', 'max:60'],
            'monthly_volume' => ['nullable', 'string', 'max:60'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', Rule::in(['landing_form', 'blog_form'])],
        ], [
            'name.required' => 'اكتب اسمك.',
            'phone.required' => 'محتاجين رقم نكلّمك عليه.',
            'phone.regex' => 'الرقم لازم يكون موبايل مصري (11 رقم يبدأ بـ 010 / 011 / 012 / 015).',
            'email.email' => 'الإيميل مش مظبوط.',
        ]);

        $lead = Lead::create($data + [
            'status' => 'new',
            'source' => $data['source'] ?? 'landing_form',
            'page_url' => mb_substr((string) $request->input('page_url', $request->headers->get('referer')), 0, 500),
            'referrer' => mb_substr((string) $request->input('referrer'), 0, 500),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->notify($lead);

        return response()->json([
            'ok' => true,
            'id' => $lead->id,
            'message' => 'وصلنا طلبك. هنكلّمك خلال ساعات العمل.',
            'whatsapp' => $this->whatsappHandoff($lead),
        ]);
    }

    /**
     * A lead that nobody sees is a lost sale, so mail is best-effort and never
     * allowed to fail the request the visitor is waiting on.
     */
    private function notify(Lead $lead): void
    {
        $to = (string) env('ZL_LEAD_NOTIFY_TO', '');
        if ($to === '') {
            return;
        }

        try {
            $body = implode("\n", array_filter([
                "طلب جديد من الموقع #{$lead->id}",
                "الاسم: {$lead->name}",
                $lead->business_name ? "المنشأة: {$lead->business_name}" : null,
                "الموبايل: {$lead->phone}",
                $lead->governorate ? "المحافظة: {$lead->governorate}" : null,
                $lead->monthly_volume ? "الكمية الشهرية: {$lead->monthly_volume}" : null,
                $lead->message ? "الرسالة: {$lead->message}" : null,
                '',
                route('admin.leads.show', $lead),
            ]));

            Mail::raw($body, fn ($m) => $m->to($to)->subject("زيلاند — طلب جديد: {$lead->name}"));
        } catch (\Throwable $e) {
            Log::warning('lead notification failed', ['lead' => $lead->id, 'error' => $e->getMessage()]);
        }
    }

    /** The pre-filled WhatsApp message the form hands back on success. */
    private function whatsappHandoff(Lead $lead): string
    {
        $digits = Arabic::whatsappDigits(Setting::get('whatsapp'));
        $text = rawurlencode(implode("\n", array_filter([
            'السلام عليكم، عايز عرض سعر بطاطس زيلاند.',
            "الاسم: {$lead->name}",
            $lead->business_name ? "المنشأة: {$lead->business_name}" : null,
            $lead->monthly_volume ? "الكمية الشهرية: {$lead->monthly_volume}" : null,
        ])));

        return "https://wa.me/{$digits}?text={$text}";
    }
}
