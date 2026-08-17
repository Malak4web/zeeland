import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

/**
 * Two independent bundles, one build:
 *   site  → the public landing page + blog (Vue 3)
 *   admin → the dashboard shell (no framework, just modules)
 *
 * Laravel reads `public/build/manifest.json` through App\Support\Vite, so there
 * is no Vite plugin in the loop — the manifest contract is all we depend on.
 */
export default defineConfig({
  // base is the SITE root, not the asset root: `/img/…` references inside the
  // components must keep resolving to public/img. The `/build/` prefix for the
  // hashed bundles is added server-side by App\Support\Vite when it reads the
  // manifest, so the two never have to agree.
  base: '/',
  plugins: [vue(), tailwindcss()],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    // publicDir stays on so `/img/…` still resolves at build time, but Laravel
    // serves those files itself — copying them into public/build would
    // duplicate every image on every build.
    copyPublicDir: false,
    manifest: 'manifest.json',
    target: 'es2022',
    cssMinify: 'lightningcss',
    rollupOptions: {
      input: {
        site: 'resources/js/site/main.js',
        admin: 'resources/js/admin/main.js',
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    // assets are requested from the Laravel origin, so tell Vite its own
    origin: 'http://localhost:5173',
  },
})
