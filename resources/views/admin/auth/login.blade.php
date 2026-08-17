<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('partials.theme-boot')
    <meta name="robots" content="noindex, nofollow">
    <title>دخول لوحة التحكم — زيلاند</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-180.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Zain:wght@700;800;900&family=Readex+Pro:wght@300;400;500;600&family=Martian+Mono:wght@400;500&display=swap">
    @vite('resources/js/admin/main.js')
</head>
<body class="grid min-h-dvh place-items-center bg-abyss px-5 py-10">

    <div class="pointer-events-none fixed inset-x-0 top-0 h-[26rem] bg-[radial-gradient(closest-side,var(--flame),transparent_72%)] opacity-[0.13]" aria-hidden="true"></div>

    <main class="relative w-full max-w-[25rem]">
        <div class="flex flex-col items-center text-cream">
            <svg width="52" height="52" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                <rect x="2.5" y="2.5" width="59" height="59" rx="17" stroke="currentColor" stroke-width="2.5"/>
                <g fill="currentColor">
                    <path d="M32 23.5 20.2 14.1a1 1 0 0 1 1.3-1.5l11.2 9.6z"/><path d="M32 23.5 41.4 11.7a1 1 0 0 1 1.5 1.3l-9.6 11.2z"/>
                    <path d="M32 23.5 43.8 32.9a1 1 0 0 1-1.3 1.5l-11.2-9.6z"/><path d="M32 23.5 22.6 35.3a1 1 0 0 1-1.5-1.3l9.6-11.2z"/>
                </g>
                <circle cx="32" cy="23.5" r="2.6" fill="currentColor"/>
                <path d="M28.4 27.4h7.2l3.1 15.4a1 1 0 0 1-1 1.2H26.3a1 1 0 0 1-1-1.2z" fill="currentColor"/>
                <g stroke="currentColor" stroke-width="2.4" stroke-linecap="round" opacity="0.9"><path d="M16.5 48.5h31"/><path d="M13.5 53.5h37"/></g>
            </svg>
            <p class="font-display mt-4 text-2xl font-black tracking-[0.16em]" style="direction:ltr">ZEELAND</p>
            <p class="mt-1 text-xs text-cream-3">لوحة التحكم</p>
        </div>

        <form method="POST" action="{{ route('admin.login.attempt') }}" class="panel mt-8 p-6">
            @csrf

            @if($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-bad/40 bg-bad/10 px-4 py-3 text-sm text-cream">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="email" class="label">الإيميل</label>
                <input id="email" name="email" type="email" inputmode="email" autocomplete="username"
                       value="{{ old('email') }}" required autofocus dir="ltr"
                       class="field text-start" placeholder="you@zeeland-foods.com"
                       @if($errors->has('email')) aria-invalid="true" @endif>
            </div>

            <div class="mt-4">
                <label for="password" class="label">كلمة السر</label>
                <input id="password" name="password" type="password" autocomplete="current-password"
                       required dir="ltr" class="field text-start"
                       @if($errors->has('password')) aria-invalid="true" @endif>
            </div>

            <label class="mt-4 flex cursor-pointer items-center gap-2.5 py-1 text-sm text-cream-2">
                <input type="checkbox" name="remember" value="1" class="size-4 accent-flame">
                فكّرني على الجهاز ده
            </label>

            <button type="submit" class="btn btn-primary mt-6 w-full">دخول</button>
        </form>

        <p class="mt-6 text-center text-2xs text-cream-3">
            <a href="{{ route('home') }}" class="transition-colors hover:text-flame-ink-hi">← رجوع للموقع</a>
        </p>
    </main>
</body>
</html>
