<?php

namespace App\Support;

/**
 * Adds stable ids to the article's H2s and hands back a table of contents.
 *
 * Done at render time rather than at save time so an old post picks up the
 * anchors too, and an editor pasting HTML never has to think about ids.
 */
final class Toc
{
    /** @return array{html: string, items: array<int, array{id: string, text: string}>} */
    public static function build(?string $html): array
    {
        $html = (string) $html;
        $items = [];
        $seen = [];

        $out = preg_replace_callback(
            '#<h2\b([^>]*)>(.*?)</h2>#is',
            function (array $m) use (&$items, &$seen) {
                $attrs = $m[1];
                $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES, 'UTF-8'));

                if ($text === '') {
                    return $m[0];
                }

                // Respect an id the author already wrote.
                if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $has)) {
                    $id = $has[1];
                } else {
                    $id = Arabic::slug($text);
                    $n = 2;
                    while (isset($seen[$id])) {
                        $id = Arabic::slug($text).'-'.$n++;
                    }
                    $attrs .= ' id="'.e($id).'"';
                }

                $seen[$id] = true;
                $items[] = ['id' => $id, 'text' => $text];

                return "<h2{$attrs}>{$m[2]}</h2>";
            },
            $html
        );

        return ['html' => (string) $out, 'items' => $items];
    }
}
