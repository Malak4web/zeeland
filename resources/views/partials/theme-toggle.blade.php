{{-- Same control as the site's ThemeToggle.vue; the behaviour lives in
     theme.js, so this is markup only.

     `$themeToggleClass` is deliberately long-winded. `@include` inherits the
     including view's variables, so a partial that reads `$class ?? default`
     picks up any `$class` that happens to be in scope — the receivables screen
     has one as a `@foreach` loop variable, and the switch rendered as a bare
     17x17 box because of it. --}}
<button type="button" data-theme-toggle
        class="theme-toggle {{ $themeToggleClass ?? 'btn btn-ghost btn-icon btn-sm' }}"
        aria-label="بدّل الوضع">
    <svg class="i-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">
        <circle cx="12" cy="12" r="4.1"/>
        <path d="M12 2.4v2M12 19.6v2M21.6 12h-2M4.4 12h-2M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4M18.8 18.8l-1.4-1.4M6.6 6.6 5.2 5.2"/>
    </svg>
    <svg class="i-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M20.5 14.3A8.6 8.6 0 0 1 9.7 3.5a8.6 8.6 0 1 0 10.8 10.8"/>
    </svg>
</button>
