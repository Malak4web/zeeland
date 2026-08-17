<?php

namespace App\Support;

use RuntimeException;

/**
 * Minimal Vite manifest reader.
 *
 * We deliberately do not use a Vite plugin: the only contract we depend on is
 * `public/build/manifest.json`, which plain Vite emits. That keeps the build
 * swappable and means nothing in the asset pipeline can break the dashboard.
 */
final class Vite
{
    private static ?array $manifest = null;

    /** Emit <link>/<script> tags for one or more manifest entries. */
    public static function tags(string ...$entries): string
    {
        if ($dev = self::devServer()) {
            $html = '<script type="module" src="'.e($dev).'/@vite/client"></script>';
            foreach ($entries as $entry) {
                $html .= '<script type="module" src="'.e($dev.'/'.$entry).'"></script>';
            }

            return $html;
        }

        $manifest = self::manifest();
        $styles = '';
        $scripts = '';

        foreach ($entries as $entry) {
            if (! isset($manifest[$entry])) {
                throw new RuntimeException("Vite entry [{$entry}] is not in the manifest. Run: npm run build");
            }
            $chunk = $manifest[$entry];

            foreach ($chunk['css'] ?? [] as $file) {
                $styles .= '<link rel="stylesheet" href="'.e(asset('build/'.$file)).'">';
            }
            foreach ($chunk['imports'] ?? [] as $import) {
                foreach ($manifest[$import]['css'] ?? [] as $file) {
                    $styles .= '<link rel="stylesheet" href="'.e(asset('build/'.$file)).'">';
                }
                $scripts .= '<link rel="modulepreload" href="'.e(asset('build/'.$manifest[$import]['file'])).'">';
            }
            $scripts .= '<script type="module" src="'.e(asset('build/'.$chunk['file'])).'"></script>';
        }

        return $styles.$scripts;
    }

    private static function devServer(): ?string
    {
        $url = env('VITE_DEV_SERVER_URL');

        return $url ? rtrim($url, '/') : null;
    }

    private static function manifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        // Vite writes .vite/manifest.json by default and <name> when
        // build.manifest is a string — accept either so a config change
        // never takes the site down.
        foreach (['build/manifest.json', 'build/.vite/manifest.json'] as $candidate) {
            $path = public_path($candidate);
            if (is_file($path)) {
                return self::$manifest = json_decode(file_get_contents($path), true) ?: [];
            }
        }

        throw new RuntimeException('Vite manifest not found in public/build. Run: npm run build');
    }
}
