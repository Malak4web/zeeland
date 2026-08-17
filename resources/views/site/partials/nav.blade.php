<a href="#main"
   class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-[60] focus:rounded-full focus:bg-flame focus:px-5 focus:py-2.5 focus:font-medium focus:text-on-flame">
    تخطَّ إلى المحتوى
</a>

<header class="sticky top-0 z-20 border-b border-navy-2 bg-abyss/88 backdrop-blur-xl">
    <div class="container-zl flex h-16 items-center justify-between gap-6 lg:h-[4.5rem]">
        <a href="{{ route('home') }}" class="text-cream transition-opacity active:opacity-70" aria-label="زيلاند — الصفحة الرئيسية">
            @include('site.partials.brandmark', ['size' => 34])
        </a>

        <nav aria-label="التنقّل الرئيسي" class="hidden lg:block">
            <ul class="flex items-center gap-1">
                <li><a href="{{ route('home') }}" class="block rounded-full px-4 py-2 text-[0.95rem] text-cream-2 transition-colors duration-300 hover:bg-navy-2 hover:text-cream">الرئيسية</a></li>
                <li><a href="{{ route('home') }}#santana" class="block rounded-full px-4 py-2 text-[0.95rem] text-cream-2 transition-colors duration-300 hover:bg-navy-2 hover:text-cream">سنتانا</a></li>
                <li><a href="{{ route('home') }}#specs" class="block rounded-full px-4 py-2 text-[0.95rem] text-cream-2 transition-colors duration-300 hover:bg-navy-2 hover:text-cream">المواصفات</a></li>
                <li>
                    <a href="{{ route('blog.index') }}"
                       @class([
                           'block rounded-full px-4 py-2 text-[0.95rem] transition-colors duration-300 hover:bg-navy-2',
                           'bg-navy-2 text-cream' => request()->routeIs('blog.*'),
                           'text-cream-2 hover:text-cream' => ! request()->routeIs('blog.*'),
                       ])>المدوّنة</a>
                </li>
            </ul>
        </nav>

        <div class="flex items-center gap-2.5">
            {{-- The blog has no bottom dock, so unlike the landing page the
                 switch has to stay reachable at every width. --}}
            @include('partials.theme-toggle', [
                'themeToggleClass' => 'size-10 rounded-full border border-navy-3 text-cream transition-[transform,border-color,background-color] duration-300 ease-out-quart hover:border-steel hover:bg-navy active:scale-90',
            ])

            <a href="{{ route('home') }}#quote"
               class="hidden rounded-full bg-flame px-5 py-2.5 text-[0.92rem] font-medium text-on-flame transition-[background-color,transform] duration-300 ease-out-quart hover:bg-flame-hi active:scale-[0.97] lg:block">
                اطلب عرض سعر
            </a>

            <a href="https://wa.me/{{ $settings['whatsapp'] }}" target="_blank" rel="noopener"
               class="grid size-10 place-items-center rounded-full bg-flame text-on-flame transition-transform duration-300 ease-out-quart active:scale-90 lg:hidden"
               aria-label="كلّمنا واتساب">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24m-3.51 3.66c-.16 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.21.89 2.39 1.01 2.55.12.17 1.72 2.62 4.16 3.68.58.25 1.03.4 1.39.51.58.19 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16s.2-1.06.14-1.16-.22-.16-.47-.28c-.24-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.55.12-.16.25-.62.79-.76.95-.14.17-.28.19-.52.07-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.24-.01-.37.11-.49.11-.11.24-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.55-1.34-.76-1.83c-.2-.48-.4-.42-.55-.42-.14 0-.3-.02-.47-.02"/>
                </svg>
            </a>

            <a href="{{ route('home') }}#quote"
               class="rounded-full border border-navy-3 px-4 py-2 text-[0.85rem] font-medium text-cream transition-transform duration-300 active:scale-95 lg:hidden">
                عرض سعر
            </a>
        </div>
    </div>
</header>
