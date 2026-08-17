<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('partials.theme-boot')
    <script>
        try {
            var _l = localStorage.getItem('zeeland_lang') || (new URLSearchParams(window.location.search)).get('lang');
            if (_l === 'en') {
                document.documentElement.setAttribute('lang', 'en');
                document.documentElement.setAttribute('dir', 'ltr');
                document.documentElement.dataset.locale = 'en';
            }
        } catch(e) {}
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seo->title }}</title>
    <meta name="description" content="{{ $seo->description }}">
    <meta name="robots" content="{{ $seo->robotsContent() }}">
    @if($seo->canonical)<link rel="canonical" href="{{ $seo->canonical }}">@endif

    <meta property="og:type" content="{{ $seo->type }}">
    <meta property="og:locale" content="ar_EG">
    <meta property="og:site_name" content="{{ $settings['site_name'] }}">
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    @if($seo->canonical)<meta property="og:url" content="{{ $seo->canonical }}">@endif
    @if($seo->absoluteImage())
        <meta property="og:image" content="{{ $seo->absoluteImage() }}">
        <meta name="twitter:image" content="{{ $seo->absoluteImage() }}">
    @endif
    @if($seo->type === 'article')
        <meta property="article:published_time" content="{{ $seo->publishedAt }}">
        <meta property="article:modified_time" content="{{ $seo->modifiedAt }}">
        @if($seo->author)<meta property="article:author" content="{{ $seo->author }}">@endif
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if($settings['twitter_handle'])<meta name="twitter:site" content="{{ $settings['twitter_handle'] }}">@endif
    @if($settings['google_site_verification'])
        <meta name="google-site-verification" content="{{ $settings['google_site_verification'] }}">
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-180.png') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $settings['site_name_en'] }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $settings['blog_title'] }}" href="{{ route('feed') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Zain:wght@300;400;700;800;900&family=Readex+Pro:wght@300;400;500;600&family=Martian+Mono:wght@400;500;600;700&display=swap">

    @stack('head')

    <script type="application/ld+json">{!! json_encode($seo->graph(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    @vite('resources/js/site/main.js')

    @if($settings['google_analytics_id'])
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['google_analytics_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $settings['google_analytics_id'] }}');
        </script>
    @endif
</head>
<body class="bg-abyss">
    @yield('body')

    <noscript>
        <div style="padding:2rem;font-family:system-ui;background:var(--ground);color:var(--ink)">
            الصفحة دي محتاجة تفعيل الجافاسكريبت. للتواصل المباشر:
            <a style="color:var(--flame-ink)" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">{{ $settings['phone'] }}</a>
        </div>
    </noscript>
</body>
</html>
