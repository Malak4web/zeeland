<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Support\Codes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::query()
            ->with('assignee')
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('assigned'), fn ($q, $a) => $q->where('assigned_to', $a))
            ->when($request->query('source'), fn ($q, $s) => $q->where('source', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Lead::query()
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')->pluck('c', 'status');

        return view('admin.leads.index', [
            'leads' => $leads,
            'counts' => $counts,
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Lead $lead)
    {
        $lead->load(['assignee', 'customer', 'notes.user']);

        return view('admin.leads.show', [
            'lead' => $lead,
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'lost_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $was = $lead->status;

        // Timestamps are set by the transition, not by the clerk — a pipeline
        // report is only as good as the dates behind it.
        if ($data['status'] !== 'new' && ! $lead->contacted_at) {
            $lead->contacted_at = now();
        }
        $lead->closed_at = in_array($data['status'], ['won', 'lost'], true) ? ($lead->closed_at ?? now()) : null;

        $lead->fill($data)->save();

        if ($was !== $lead->status) {
            Activity::log('updated', "غيّر حالة الطلب #{$lead->id} من ".(Lead::STATUSES[$was] ?? $was)." لـ {$lead->statusLabel()}", $lead);
        }

        return back()->with('ok', 'اتحدّث.');
    }

    public function storeNote(Request $request, Lead $lead)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $lead->notes()->create($data + ['user_id' => auth()->id()]);

        if (! $lead->contacted_at) {
            $lead->forceFill(['contacted_at' => now()])->saveQuietly();
        }

        return back()->with('ok', 'الملاحظة اتسجّلت.');
    }

    /**
     * Turn a lead into a customer without retyping anything. The lead is kept
     * and linked — the pipeline report needs to know which leads converted.
     */
    public function convert(Request $request, Lead $lead)
    {
        abort_if(! auth()->user()->can_('customers.edit'), 403);

        if ($lead->customer_id) {
            return redirect()->route('admin.customers.show', $lead->customer_id)
                ->with('ok', 'العميل ده متسجّل قبل كده.');
        }

        $customer = DB::transaction(function () use ($lead) {
            $customer = Customer::create([
                'code' => Codes::customer(),
                'name' => $lead->business_name ?: $lead->name,
                'contact_name' => $lead->name,
                'business_type' => $this->mapType($lead->business_type),
                'phone' => $lead->phone,
                'email' => $lead->email,
                'governorate' => $lead->governorate,
                'notes' => $lead->message,
                'lead_id' => $lead->id,
                'created_by' => auth()->id(),
            ]);

            $lead->forceFill([
                'customer_id' => $customer->id,
                'status' => 'won',
                'closed_at' => now(),
                'contacted_at' => $lead->contacted_at ?? now(),
            ])->save();

            return $customer;
        });

        Activity::log('created', "حوّل الطلب #{$lead->id} لعميل {$customer->code}", $customer);

        return redirect()->route('admin.customers.edit', $customer)
            ->with('ok', 'اتحوّل لعميل. كمّل بيانات التعامل.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        Activity::log('deleted', "مسح الطلب #{$lead->id} ({$lead->name})", $lead);

        return redirect()->route('admin.leads.index')->with('ok', 'اتمسح.');
    }

    /** The form's business_type values do not all match the customer list. */
    private function mapType(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (array_key_exists($value, Customer::TYPES)) {
            return $value;
        }

        return match (true) {
            str_contains($value, 'مطعم') => 'restaurant',
            str_contains($value, 'كافيه') || str_contains($value, 'كافيتريا') => 'cafe',
            str_contains($value, 'فندق') => 'hotel',
            str_contains($value, 'موزّع') || str_contains($value, 'موزع') || str_contains($value, 'جملة') => 'distributor',
            str_contains($value, 'كاترينج') => 'catering',
            str_contains($value, 'سوبر') || str_contains($value, 'ماركت') => 'market',
            default => 'other',
        };
    }
}
