{{-- Resolves the theme before the first paint.

     This has to be inline and synchronous: a deferred module would let the
     browser paint the default palette first, and the visitor would see the
     page flash from navy to daylight on every single navigation.

     The two media-scoped tags below are the no-JS answer — the browser picks
     one by itself. Once a choice is stored, theme.js mutes the loser. --}}
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="#010e1e" data-scheme="dark">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="#f3f8fd" data-scheme="light">
<script>
(function () {
    var root = document.documentElement, theme = 'dark';
    try {
        var stored = localStorage.getItem('zl-theme');
        theme = stored === 'light' || stored === 'dark'
            ? stored
            : (matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
        if (stored) {
            var tags = document.querySelectorAll('meta[name="theme-color"][data-scheme]');
            for (var i = 0; i < tags.length; i++) {
                tags[i].media = tags[i].getAttribute('data-scheme') === theme ? 'all' : 'not all';
            }
        }
    } catch (e) {
        /* storage blocked — fall back to the brand default */
    }
    root.setAttribute('data-theme', theme);
})();
</script>
