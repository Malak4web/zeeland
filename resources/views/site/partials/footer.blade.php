<footer class="border-t border-navy-2 bg-abyss">
    <div class="container-zl py-14 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <span class="inline-block text-cream">@include('site.partials.brandmark', ['size' => 38])</span>
                <p class="prose-zl mt-5 max-w-[42ch]">{{ $settings['site_tagline'] }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ $settings['whatsapp'] }}" target="_blank" rel="noopener"
                       class="rounded-full bg-flame px-5 py-2.5 text-[0.9rem] font-medium text-on-flame transition-colors hover:bg-flame-hi">كلّمنا واتساب</a>
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}"
                       class="rounded-full border border-navy-3 px-5 py-2.5 text-[0.9rem] font-medium text-cream transition-colors hover:bg-navy-2">
                        <span class="num">{{ $settings['phone'] }}</span>
                    </a>
                </div>
            </div>

            <nav class="lg:col-span-3" aria-labelledby="f-nav">
                <h2 id="f-nav" class="text-[0.85rem] font-semibold text-cream-3">الصفحة</h2>
                <ul class="mt-4 flex flex-col gap-1 text-[0.95rem]">
                    <li><a class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi" href="{{ route('home') }}">الرئيسية</a></li>
                    <li><a class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi" href="{{ route('home') }}#santana">سنتانا</a></li>
                    <li><a class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi" href="{{ route('home') }}#specs">المواصفات</a></li>
                    <li><a class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi" href="{{ route('blog.index') }}">المدوّنة</a></li>
                    <li><a class="inline-block py-1 text-cream-2 transition-colors hover:text-flame-ink-hi" href="{{ route('home') }}#quote">اطلب عرض سعر</a></li>
                </ul>
            </nav>

            <div class="lg:col-span-4">
                <h2 class="text-[0.85rem] font-semibold text-cream-3">التواصل</h2>
                <ul class="mt-4 flex flex-col gap-2 text-[0.95rem] text-cream-2">
                    @if($settings['email'])
                        <li><a class="inline-block py-1 transition-colors hover:text-flame-ink-hi ltr-iso" href="mailto:{{ $settings['email'] }}">{{ $settings['email'] }}</a></li>
                    @endif
                    <li class="py-1">{{ $settings['address'] }}</li>
                    <li class="py-1">{{ $settings['hours'] }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-navy-2 pt-6 text-[0.8rem] text-cream-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="ltr-iso">© {{ date('Y') }} {{ $settings['site_name_en'] }} Foods. All rights reserved.</p>
            <a href="{{ route('feed') }}" class="inline-block py-1 transition-colors hover:text-flame-ink-hi">RSS</a>
        </div>
    </div>
</footer>
