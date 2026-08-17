@extends('layouts.admin')

@section('title', 'الإعدادات')
@section('subtitle', 'كل النصوص والأرقام اللي الموقع بيقرأها')

@section('actions')
    <button type="submit" form="settings-form" class="btn btn-primary btn-sm">حفظ</button>
@endsection

@section('content')
    <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-4 lg:grid-cols-12">
        @csrf @method('PATCH')

        <div class="flex flex-col gap-4 lg:col-span-7">

            <section class="panel p-5" aria-labelledby="id-h">
                <h2 id="id-h" class="text-sm font-semibold text-cream">الهوية</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="site_name" class="label">اسم الموقع (عربي)</label>
                        <input id="site_name" name="site_name" value="{{ old('site_name', $values['site_name']) }}" required class="field">
                    </div>
                    <div>
                        <label for="site_name_en" class="label">الاسم بالإنجليزي</label>
                        <input id="site_name_en" name="site_name_en" dir="ltr" value="{{ old('site_name_en', $values['site_name_en']) }}" class="field text-start">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="site_tagline" class="label">الوصف المختصر</label>
                        <input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $values['site_tagline']) }}" class="field">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="site_url" class="label">الدومين <span class="text-flame-ink">*</span></label>
                        <input id="site_url" name="site_url" type="url" dir="ltr" required
                               value="{{ old('site_url', $values['site_url']) }}" class="field num text-start" placeholder="https://zeeland-foods.com">
                        <p class="hint">
                            <strong class="text-warn">مهم للسيو.</strong>
                            ده الرابط اللي بيتكتب في canonical وفي خريطة الموقع. لازم يبقى الدومين الحقيقي بـ https
                            قبل ما تفتح الموقع للناس، وإلا جوجل هيفهرس نسختين من كل صفحة.
                        </p>
                    </div>
                </div>
            </section>

            <section class="panel p-5" aria-labelledby="seo-h">
                <h2 id="seo-h" class="text-sm font-semibold text-cream">السيو الافتراضي</h2>
                <p class="mt-1 text-2xs leading-[1.8] text-cream-3">بيتستخدم في الصفحة الرئيسية وفي أي صفحة مالهاش سيو خاص.</p>

                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <label for="meta_title" class="label">عنوان الميتا <span class="text-flame-ink">*</span></label>
                        <input id="meta_title" name="meta_title" required value="{{ old('meta_title', $values['meta_title']) }}" class="field">
                    </div>
                    <div>
                        <label for="meta_description" class="label">وصف الميتا <span class="text-flame-ink">*</span></label>
                        <textarea id="meta_description" name="meta_description" rows="3" required class="field">{{ old('meta_description', $values['meta_description']) }}</textarea>
                    </div>
                    <div>
                        <label for="og_image" class="label">صورة المشاركة</label>
                        <input id="og_image" name="og_image" dir="ltr" value="{{ old('og_image', $values['og_image']) }}" class="field num text-start text-xs">
                        <p class="hint">اللي بتظهر لما حد يبعت رابط الموقع على واتساب أو فيسبوك.</p>
                    </div>
                    <div>
                        <label for="robots_extra" class="label">سطور إضافية في robots.txt</label>
                        <textarea id="robots_extra" name="robots_extra" rows="3" dir="ltr" class="field num text-start text-xs"
                                  placeholder="Disallow: /private">{{ old('robots_extra', $values['robots_extra']) }}</textarea>
                        <p class="hint">
                            <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="inline-block py-1 text-flame-ink-hi">شوف robots.txt</a> ·
                            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="inline-block py-1 text-flame-ink-hi">شوف sitemap.xml</a>
                        </p>
                    </div>
                </div>
            </section>

            <section class="panel p-5" aria-labelledby="track-h">
                <h2 id="track-h" class="text-sm font-semibold text-cream">أدوات القياس</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="google_site_verification" class="label">كود تحقّق Search Console</label>
                        <input id="google_site_verification" name="google_site_verification" dir="ltr"
                               value="{{ old('google_site_verification', $values['google_site_verification']) }}" class="field num text-start text-xs">
                        <p class="hint">من Google Search Console → HTML tag، انسخ قيمة content بس.</p>
                    </div>
                    <div>
                        <label for="google_analytics_id" class="label">Google Analytics</label>
                        <input id="google_analytics_id" name="google_analytics_id" dir="ltr"
                               value="{{ old('google_analytics_id', $values['google_analytics_id']) }}" class="field num text-start" placeholder="G-XXXXXXX">
                    </div>
                    <div>
                        <label for="meta_pixel_id" class="label">Meta Pixel</label>
                        <input id="meta_pixel_id" name="meta_pixel_id" dir="ltr"
                               value="{{ old('meta_pixel_id', $values['meta_pixel_id']) }}" class="field num text-start">
                    </div>
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-4 lg:col-span-5">

            <section class="panel p-5" aria-labelledby="con-h">
                <h2 id="con-h" class="text-sm font-semibold text-cream">التواصل</h2>
                <p class="mt-1 text-2xs leading-[1.8] text-cream-3">الأرقام دي بتظهر على اللاندينج بيج، في الفوتر، وفي إذن التسليم.</p>

                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <label for="phone" class="label">التليفون <span class="text-flame-ink">*</span></label>
                        <input id="phone" name="phone" dir="ltr" required value="{{ old('phone', $values['phone']) }}" class="field num text-start">
                    </div>
                    <div>
                        <label for="whatsapp" class="label">واتساب <span class="text-flame-ink">*</span></label>
                        <input id="whatsapp" name="whatsapp" dir="ltr" required value="{{ old('whatsapp', $values['whatsapp']) }}" class="field num text-start" placeholder="201001234567">
                        <p class="hint">بكود الدولة من غير + ولا مسافات.</p>
                    </div>
                    <div>
                        <label for="email" class="label">الإيميل</label>
                        <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $values['email']) }}" class="field text-start">
                    </div>
                    <div>
                        <label for="address" class="label">العنوان</label>
                        <input id="address" name="address" value="{{ old('address', $values['address']) }}" class="field">
                    </div>
                    <div>
                        <label for="hours" class="label">مواعيد العمل</label>
                        <input id="hours" name="hours" value="{{ old('hours', $values['hours']) }}" class="field">
                    </div>
                </div>
            </section>

            <section class="panel p-5" aria-labelledby="com-h">
                <h2 id="com-h" class="text-sm font-semibold text-cream">التجارة</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="currency" class="label">العملة <span class="text-flame-ink">*</span></label>
                        <input id="currency" name="currency" required value="{{ old('currency', $values['currency']) }}" class="field">
                    </div>
                    <div>
                        <label for="default_pack_price" class="label">سعر الشيكارة الافتراضي</label>
                        <input id="default_pack_price" name="default_pack_price" type="number" step="0.01" min="0" dir="ltr"
                               value="{{ old('default_pack_price', $values['default_pack_price']) }}" class="field num text-start">
                    </div>
                </div>
            </section>

            <section class="panel p-5" aria-labelledby="blog-h">
                <h2 id="blog-h" class="text-sm font-semibold text-cream">المدوّنة</h2>
                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <label for="blog_title" class="label">عنوان المدوّنة <span class="text-flame-ink">*</span></label>
                        <input id="blog_title" name="blog_title" required value="{{ old('blog_title', $values['blog_title']) }}" class="field">
                    </div>
                    <div>
                        <label for="blog_description" class="label">وصف المدوّنة</label>
                        <textarea id="blog_description" name="blog_description" rows="3" class="field">{{ old('blog_description', $values['blog_description']) }}</textarea>
                    </div>
                    <div>
                        <label for="posts_per_page" class="label">مقالات في الصفحة</label>
                        <input id="posts_per_page" name="posts_per_page" type="number" min="3" max="48" dir="ltr" required
                               value="{{ old('posts_per_page', $values['posts_per_page']) }}" class="field num text-start">
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary">احفظ الإعدادات</button>
        </div>
    </form>
@endsection
