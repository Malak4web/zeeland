<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['values' => Setting::all_()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'site_name_en' => ['nullable', 'string', 'max:80'],
            'site_tagline' => ['nullable', 'string', 'max:160'],
            'site_url' => ['required', 'url', 'max:200'],

            'meta_title' => ['required', 'string', 'max:200'],
            'meta_description' => ['required', 'string', 'max:320'],
            'og_image' => ['nullable', 'string', 'max:300'],

            'google_site_verification' => ['nullable', 'string', 'max:200'],
            'google_analytics_id' => ['nullable', 'string', 'max:40'],
            'meta_pixel_id' => ['nullable', 'string', 'max:40'],
            'robots_extra' => ['nullable', 'string', 'max:2000'],

            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'address' => ['nullable', 'string', 'max:200'],
            'hours' => ['nullable', 'string', 'max:120'],

            'currency' => ['required', 'string', 'max:10'],
            'default_pack_price' => ['nullable', 'numeric', 'min:0'],

            'blog_title' => ['required', 'string', 'max:120'],
            'blog_description' => ['nullable', 'string', 'max:320'],
            'posts_per_page' => ['required', 'integer', 'min:3', 'max:48'],
        ], [
            'site_url.url' => 'الدومين لازم يكون رابط كامل يبدأ بـ https://',
            'meta_title.required' => 'عنوان الميتا الافتراضي مطلوب.',
            'meta_description.required' => 'وصف الميتا الافتراضي مطلوب.',
        ]);

        $data['site_url'] = rtrim($data['site_url'], '/');
        $data['whatsapp'] = preg_replace('/\D+/', '', $data['whatsapp']);

        Setting::putMany($data);
        Activity::log('updated', 'عدّل إعدادات الموقع');

        return back()->with('ok', 'الإعدادات اتحفظت.');
    }
}
