@php
    $u = auth()->user();

    /**
     * One nav definition drives the sidebar, the mobile dock and the overflow
     * sheet. An item nobody can open never renders, so the nav can't advertise
     * a 403.
     */
    $icon = [
        'grid'    => '<path d="M4 4h7v7H4zM13 4h7v5h-7zM13 11h7v9h-7zM4 13h7v7H4z"/>',
        'inbox'   => '<path d="M3 12h4l2 3h6l2-3h4"/><path d="M5 5h14l2 7v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5z"/>',
        'users'   => '<path d="M16 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20"/><circle cx="9" cy="7" r="3.2"/><path d="M17 4.2a3.2 3.2 0 0 1 0 5.9M22 20v-1.5a4 4 0 0 0-3-3.8"/>',
        'box'     => '<path d="M12 3 21 7.5v9L12 21 3 16.5v-9z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
        'wallet'  => '<path d="M3 8a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M16 12.5h3.5"/>',
        'doc'     => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
        'tag'     => '<path d="M3 12V5a2 2 0 0 1 2-2h7l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
        'chart'   => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'link'    => '<path d="M10.5 13.5a4 4 0 0 0 5.7 0l3-3a4 4 0 1 0-5.7-5.7l-1.7 1.7"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-3 3a4 4 0 1 0 5.7 5.7l1.7-1.7"/>',
        'gear'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1v.2a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-2.8-1.1l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3.5 15a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.1-2.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 10 4.5a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7h.2a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.3.8z"/>',
        'shield'  => '<path d="M12 3 20 6v6c0 4.5-3.2 7.9-8 9-4.8-1.1-8-4.5-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5M12 8v4.5l3 1.8"/>',
        'more'    => '<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>',
    ];

    $groups = array_values(array_filter([
        [
            'label' => 'التشغيل',
            'items' => array_values(array_filter([
                $u->can_('leads.view') || $u->isAdmin() ? ['r' => 'admin.dashboard', 'l' => 'لوحة اليوم', 'i' => 'grid', 'm' => 'admin.dashboard'] : null,
                $u->can_('leads.view')     ? ['r' => 'admin.leads.index',     'l' => 'طلبات الموقع', 'i' => 'inbox',  'm' => 'admin.leads.*'] : null,
                $u->can_('customers.view') ? ['r' => 'admin.customers.index', 'l' => 'العملاء',      'i' => 'users',  'm' => 'admin.customers.*'] : null,
                $u->can_('orders.view')    ? ['r' => 'admin.orders.index',    'l' => 'الأوردرات',    'i' => 'box',    'm' => 'admin.orders.*'] : null,
                $u->can_('payments.view')  ? ['r' => 'admin.payments.index',  'l' => 'الدفعات',      'i' => 'wallet', 'm' => 'admin.payments.*'] : null,
            ])),
        ],
        [
            'label' => 'المحتوى',
            'items' => array_values(array_filter([
                $u->can_('blog.view') ? ['r' => 'admin.posts.index',    'l' => 'المقالات',   'i' => 'doc',  'm' => 'admin.posts.*'] : null,
                $u->can_('blog.edit') ? ['r' => 'admin.taxonomy.index', 'l' => 'الأقسام والوسوم', 'i' => 'tag', 'm' => 'admin.taxonomy.*'] : null,
                $u->can_('seo.view')  ? ['r' => 'admin.redirects.index','l' => 'تحويلات الروابط', 'i' => 'link', 'm' => 'admin.redirects.*'] : null,
            ])),
        ],
        [
            'label' => 'الإدارة',
            'items' => array_values(array_filter([
                $u->can_('reports.view')  ? ['r' => 'admin.reports.index', 'l' => 'التقارير', 'i' => 'chart',  'm' => 'admin.reports.*'] : null,
                $u->can_('products.view') ? ['r' => 'admin.products.index','l' => 'الأصناف',  'i' => 'box',    'm' => 'admin.products.*'] : null,
                $u->can_('settings.edit') ? ['r' => 'admin.users.index',   'l' => 'المستخدمين','i' => 'shield', 'm' => 'admin.users.*'] : null,
                $u->can_('settings.edit') ? ['r' => 'admin.activity.index','l' => 'سجل النشاط','i' => 'history','m' => 'admin.activity.*'] : null,
                $u->can_('settings.edit') ? ['r' => 'admin.settings.edit', 'l' => 'الإعدادات', 'i' => 'gear',   'm' => 'admin.settings.*'] : null,
            ])),
        ],
    ], fn ($g) => count($g['items']) > 0));

    // The dock carries the four most-used destinations; everything else goes
    // behind "أكتر". Which four depends on what this role actually does.
    $flat = collect($groups)->flatMap(fn ($g) => $g['items'])->values();
    $dock = $flat->take(4);
    $rest = $flat->slice(4);
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('partials.theme-boot')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'لوحة التحكم') — زيلاند</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ route('admin.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-180.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="زيلاند إدارة">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Zain:wght@700;800;900&family=Readex+Pro:wght@300;400;500;600&family=Martian+Mono:wght@400;500;600;700&display=swap">

    @vite('resources/js/admin/main.js')
</head>
<body class="min-h-dvh bg-abyss">

<a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-[70] focus:rounded-lg focus:bg-flame focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-on-flame">تخطَّ إلى المحتوى</a>

{{-- ─────────────────────────────────────────────── sidebar (desktop only) --}}
<aside class="a-sidebar fixed inset-y-0 start-0 z-30 hidden w-[16.5rem] flex-col border-e border-navy-2 bg-[var(--chrome)] lg:flex">
    <div class="flex h-16 items-center gap-2.5 border-b border-navy-2 px-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-cream">
            <svg width="30" height="30" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                <rect x="2.5" y="2.5" width="59" height="59" rx="17" stroke="currentColor" stroke-width="2.5"/>
                <g fill="currentColor">
                    <path d="M32 23.5 20.2 14.1a1 1 0 0 1 1.3-1.5l11.2 9.6z"/><path d="M32 23.5 41.4 11.7a1 1 0 0 1 1.5 1.3l-9.6 11.2z"/>
                    <path d="M32 23.5 43.8 32.9a1 1 0 0 1-1.3 1.5l-11.2-9.6z"/><path d="M32 23.5 22.6 35.3a1 1 0 0 1-1.5-1.3l9.6-11.2z"/>
                </g>
                <circle cx="32" cy="23.5" r="2.6" fill="currentColor"/>
                <path d="M28.4 27.4h7.2l3.1 15.4a1 1 0 0 1-1 1.2H26.3a1 1 0 0 1-1-1.2z" fill="currentColor"/>
                <g stroke="currentColor" stroke-width="2.4" stroke-linecap="round" opacity="0.9"><path d="M16.5 48.5h31"/><path d="M13.5 53.5h37"/></g>
            </svg>
            <span class="flex flex-col leading-none">
                <span class="font-display text-lg font-black tracking-[0.14em]" style="direction:ltr">ZEELAND</span>
                <span class="mt-1 text-2xs text-cream-3">لوحة التحكم</span>
            </span>
        </a>
    </div>

    <nav aria-label="أقسام اللوحة" class="thin-scroll flex-1 overflow-y-auto px-2.5 py-3">
        @foreach($groups as $group)
            <p class="a-nav-group">{{ $group['label'] }}</p>
            <ul class="mt-1.5 flex flex-col gap-0.5">
                @foreach($group['items'] as $item)
                    <li>
                        <a href="{{ route($item['r']) }}" class="a-nav-item"
                           @if(request()->routeIs($item['m'])) aria-current="page" @endif>
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-cream-3" aria-hidden="true">{!! $icon[$item['i']] !!}</svg>
                            <span>{{ $item['l'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="border-t border-navy-2 p-2.5">
        <div class="flex items-center gap-2.5 rounded-xl px-2 py-2">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-navy-2 text-sm font-semibold text-cream">{{ mb_substr($u->name, 0, 1) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm text-cream">{{ $u->name }}</span>
                <span class="block text-2xs text-cream-3">{{ $u->roleLabel() }}</span>
            </span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-icon btn-sm" aria-label="تسجيل الخروج" title="تسجيل الخروج">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                </button>
            </form>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="a-nav-item mt-1 text-xs">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
            افتح الموقع
        </a>
    </div>
</aside>

<div class="lg:ps-[16.5rem]">
    {{-- ─────────────────────────────────────────────────────────── topbar --}}
    <header data-hide-on-scroll
            class="a-topbar sticky top-0 z-20 border-b border-navy-2 bg-abyss/92 backdrop-blur-xl transition-transform duration-300 ease-out-quart lg:translate-y-0!">
        <div class="flex h-14 items-center gap-3 px-4 lg:h-16 lg:px-7">
            @hasSection('back')
                <a href="@yield('back')" class="btn btn-ghost btn-icon btn-sm shrink-0 lg:hidden" aria-label="رجوع">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                </a>
            @endif

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold text-cream lg:text-lg">@yield('title', 'لوحة التحكم')</h1>
                @hasSection('subtitle')
                    <p class="truncate text-2xs text-cream-3">@yield('subtitle')</p>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-2">
                @yield('actions')
                @include('partials.theme-toggle')
            </div>
        </div>
    </header>

    {{-- ──────────────────────────────────────────────────────────── flash --}}
    <div class="px-4 pt-4 lg:px-7">
        @if(session('ok'))
            <div data-flash="ok" role="status" class="mb-4 flex items-start gap-3 rounded-xl border border-good/35 bg-good/10 px-4 py-3">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-good" aria-hidden="true"><path d="m4.5 12.5 5 5 10-11"/></svg>
                <p class="flex-1 text-sm text-cream">{{ session('ok') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div data-flash="error" role="alert" class="mb-4 flex items-start gap-3 rounded-xl border border-bad/40 bg-bad/10 px-4 py-3">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" class="mt-0.5 shrink-0 text-bad" aria-hidden="true"><path d="M12 6v8M12 18h.01"/></svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-cream">فيه {{ $errors->count() }} حاجة محتاجة تظبيط</p>
                    <ul class="mt-1 flex flex-col gap-0.5">
                        @foreach($errors->all() as $error)
                            <li class="text-xs text-cream-2">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" data-flash-close class="btn btn-ghost btn-icon btn-sm shrink-0" aria-label="إخفاء">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        @endif
    </div>

    <main id="content" class="px-4 pb-[calc(5.5rem+env(safe-area-inset-bottom))] lg:px-7 lg:pb-12">
        @yield('content')
    </main>
</div>

{{-- ──────────────────────────────────────────── mobile dock (thumb reach) --}}
<nav class="a-dock fixed inset-x-0 bottom-0 z-40 border-t border-navy-2 bg-abyss/95 backdrop-blur-xl lg:hidden"
     style="padding-bottom:env(safe-area-inset-bottom)" aria-label="التنقّل السريع">
    <ul class="grid grid-cols-5">
        @foreach($dock as $item)
            <li>
                <a href="{{ route($item['r']) }}"
                   class="flex flex-col items-center gap-1 py-2.5 text-2xs transition-colors {{ request()->routeIs($item['m']) ? 'text-flame-ink' : 'text-cream-3' }}"
                   @if(request()->routeIs($item['m'])) aria-current="page" @endif>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon[$item['i']] !!}</svg>
                    <span class="max-w-full truncate px-1">{{ $item['l'] }}</span>
                </a>
            </li>
        @endforeach
        <li>
            <button type="button" data-sheet-open="more"
                    class="flex w-full flex-col items-center gap-1 py-2.5 text-2xs text-cream-3 transition-colors active:text-cream">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon['more'] !!}</svg>
                <span>أكتر</span>
            </button>
        </li>
    </ul>
</nav>

<dialog class="sheet lg:hidden" data-sheet="more" aria-labelledby="more-h">
    <div class="sheet-panel">
        <div class="sheet-grip" aria-hidden="true"></div>
        <h2 id="more-h" class="px-1 pb-1 text-base font-semibold text-cream">كل الأقسام</h2>
        <p class="px-1 pb-4 text-xs text-cream-3">{{ $u->name }} · {{ $u->roleLabel() }}</p>

        <ul class="flex flex-col">
            @foreach($rest as $item)
                <li>
                    <a href="{{ route($item['r']) }}" class="flex items-center gap-3 border-b border-navy-2 py-3.5 text-cream">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="text-cream-3" aria-hidden="true">{!! $icon[$item['i']] !!}</svg>
                        <span class="flex-1">{{ $item['l'] }}</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="text-cream-3" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-3 border-b border-navy-2 py-3.5 text-cream">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="text-cream-3" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                    <span class="flex-1">افتح الموقع</span>
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('admin.logout') }}" class="mt-5">
            @csrf
            <button type="submit" class="btn btn-ghost w-full">تسجيل الخروج</button>
        </form>
    </div>
</dialog>

{{-- Destructive actions confirm here — native <dialog>, real sentence. --}}
<dialog class="sheet" data-confirm-dialog aria-labelledby="confirm-h">
    <div class="sheet-panel">
        <div class="sheet-grip" aria-hidden="true"></div>
        <h2 id="confirm-h" class="text-base font-semibold text-cream">متأكّد؟</h2>
        <p data-confirm-message class="mt-2 text-sm leading-[1.9] text-cream-2"></p>
        <div class="mt-6 flex gap-2.5">
            <button type="button" data-confirm-accept class="btn btn-primary flex-1">أيوة، كمّل</button>
            <button type="button" data-sheet-close class="btn btn-ghost flex-1">إلغاء</button>
        </div>
    </div>
</dialog>

@stack('sheets')
</body>
</html>
