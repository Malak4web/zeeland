<?php

namespace App\Support;

/**
 * The live SEO assistant.
 *
 * One implementation, server side. The editor's panel calls this over fetch on
 * a debounce, so the score an editor sees while typing is the exact score that
 * gets stored — there is no second copy of these rules in JavaScript to drift.
 *
 * Every check returns pass / warn / fail plus a hint written for someone who
 * does not do SEO for a living.
 */
final class SeoAnalyzer
{
    /** Ranges Google actually truncates at, measured in characters. */
    private const TITLE_MIN = 30;

    private const TITLE_MAX = 60;

    private const DESC_MIN = 110;

    private const DESC_MAX = 160;

    private const MIN_WORDS = 300;

    public static function analyze(array $post): array
    {
        $title = trim((string) ($post['meta_title'] ?: $post['title'] ?? ''));
        $rawTitle = trim((string) ($post['title'] ?? ''));
        $desc = trim((string) ($post['meta_description'] ?? ''));
        $body = (string) ($post['body'] ?? '');
        $slug = trim((string) ($post['slug'] ?? ''));
        $keyword = trim((string) ($post['focus_keyword'] ?? ''));
        $excerpt = trim((string) ($post['excerpt'] ?? ''));
        $cover = trim((string) ($post['cover_image'] ?? ''));
        $coverAlt = trim((string) ($post['cover_alt'] ?? ''));

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($body)));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));
        $k = $keyword !== '' ? Arabic::fold($keyword) : '';

        $checks = [];

        /* ---------------------------------------------------- the snippet */

        $len = mb_strlen($title);
        $checks[] = self::check(
            'title', 'عنوان الصفحة',
            $len === 0 ? 'fail' : ($len < self::TITLE_MIN || $len > self::TITLE_MAX ? 'warn' : 'pass'),
            match (true) {
                $len === 0 => 'مفيش عنوان.',
                $len < self::TITLE_MIN => "قصير ({$len} حرف) — جوجل بيدي مساحة لـ ".self::TITLE_MAX.' حرف، استغلها.',
                $len > self::TITLE_MAX => "طويل ({$len} حرف) — جوجل هيقصّه بعد ".self::TITLE_MAX.'.',
                default => "الطول مظبوط ({$len} حرف).",
            },
            ['length' => $len, 'min' => self::TITLE_MIN, 'max' => self::TITLE_MAX],
            weight: 3,
        );

        $len = mb_strlen($desc);
        $checks[] = self::check(
            'description', 'وصف الميتا',
            $len === 0 ? 'fail' : ($len < self::DESC_MIN || $len > self::DESC_MAX ? 'warn' : 'pass'),
            match (true) {
                $len === 0 => 'من غير وصف، جوجل بيقتطع سطر عشوائي من المقال.',
                $len < self::DESC_MIN => "قصير ({$len} حرف) — الأفضل بين ".self::DESC_MIN.' و '.self::DESC_MAX.'.',
                $len > self::DESC_MAX => "طويل ({$len} حرف) — هيتقصّ في نتايج البحث.",
                default => "الطول مظبوط ({$len} حرف).",
            },
            ['length' => $len, 'min' => self::DESC_MIN, 'max' => self::DESC_MAX],
            weight: 3,
        );

        /* ------------------------------------------------ the keyword ---- */

        if ($k === '') {
            $checks[] = self::check('keyword', 'الكلمة المفتاحية', 'warn',
                'حدّد كلمة مفتاحية عشان أقدر أقيس باقي النقط.', weight: 2);
        } else {
            $checks[] = self::check('keyword_title', 'الكلمة في العنوان',
                str_contains(Arabic::fold($title), $k) ? 'pass' : 'fail',
                str_contains(Arabic::fold($title), $k)
                    ? 'موجودة في العنوان.'
                    : "«{$keyword}» مش في العنوان — دي أقوى إشارة عندك.",
                weight: 3);

            $checks[] = self::check('keyword_desc', 'الكلمة في الوصف',
                str_contains(Arabic::fold($desc), $k) ? 'pass' : 'warn',
                str_contains(Arabic::fold($desc), $k)
                    ? 'موجودة في الوصف.'
                    : 'حطّها في الوصف — جوجل بيعلّمها بالأسود في النتايج.',
                weight: 2);

            $checks[] = self::check('keyword_slug', 'الكلمة في الرابط',
                str_contains(Arabic::fold(str_replace('-', ' ', $slug)), $k) ? 'pass' : 'warn',
                str_contains(Arabic::fold(str_replace('-', ' ', $slug)), $k)
                    ? 'الرابط فيه الكلمة.'
                    : 'الرابط مافيهوش الكلمة المفتاحية.',
                weight: 2);

            $intro = mb_substr($text, 0, 300);
            $checks[] = self::check('keyword_intro', 'الكلمة في أول فقرة',
                str_contains(Arabic::fold($intro), $k) ? 'pass' : 'warn',
                str_contains(Arabic::fold($intro), $k)
                    ? 'ظهرت بدري في المقال.'
                    : 'اذكرها في أول 300 حرف عشان القارئ وجوجل يعرفوا الموضوع فورًا.',
                weight: 2);

            $occurrences = $k === '' ? 0 : mb_substr_count(Arabic::fold($text), $k);
            $density = $words > 0 ? round($occurrences / $words * 100, 2) : 0;
            $checks[] = self::check('keyword_density', 'كثافة الكلمة',
                match (true) {
                    $occurrences === 0 => 'fail',
                    $density > 3.5 => 'warn',
                    $density < 0.4 => 'warn',
                    default => 'pass',
                },
                match (true) {
                    $occurrences === 0 => 'الكلمة مش موجودة في المقال خالص.',
                    $density > 3.5 => "مكرّرة {$occurrences} مرة ({$density}%) — ده حشو، قلّلها.",
                    $density < 0.4 => "ظهرت {$occurrences} مرة بس ({$density}%) — زوّدها شوية طبيعيًا.",
                    default => "{$occurrences} مرة ({$density}%) — كويس.",
                },
                ['count' => $occurrences, 'density' => $density],
                weight: 2);
        }

        /* -------------------------------------------------- the content -- */

        $checks[] = self::check('length', 'طول المقال',
            match (true) {
                $words < 150 => 'fail',
                $words < self::MIN_WORDS => 'warn',
                default => 'pass',
            },
            $words < self::MIN_WORDS
                ? "{$words} كلمة — المقالات اللي بتترتب في العربي بتبدأ من ".self::MIN_WORDS.' كلمة.'
                : "{$words} كلمة — كفاية.",
            ['words' => $words],
            weight: 3);

        preg_match_all('/<h([23])\b/i', $body, $headings);
        $h2 = count(array_filter($headings[1] ?? [], fn ($l) => $l === '2'));
        $checks[] = self::check('headings', 'عناوين فرعية (H2)',
            $h2 === 0 ? ($words > 300 ? 'fail' : 'warn') : 'pass',
            $h2 === 0
                ? 'مفيش عناوين فرعية — قسّم المقال بـ H2، ده بيجيب مقتطفات في جوجل.'
                : "فيه {$h2} عنوان فرعي.",
            ['h2' => $h2],
            weight: 2);

        $site = rtrim((string) \App\Models\Setting::get('site_url'), '/');
        preg_match_all('/<a\b[^>]*href=["\']([^"\']+)["\']/i', $body, $links);
        $internal = 0;
        $external = 0;
        foreach ($links[1] ?? [] as $href) {
            if (str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }
            if (str_starts_with($href, '/') || ($site && str_starts_with($href, $site))) {
                $internal++;
            } else {
                $external++;
            }
        }

        $checks[] = self::check('internal_links', 'روابط داخلية',
            $internal === 0 ? 'warn' : 'pass',
            $internal === 0
                ? 'ماتسيبش المقال معزول — حط رابط أو اتنين لصفحات تانية عندك.'
                : "{$internal} رابط داخلي.",
            ['count' => $internal],
            weight: 2);

        $checks[] = self::check('external_links', 'روابط خارجية',
            $external === 0 ? 'warn' : 'pass',
            $external === 0
                ? 'رابط لمصدر محترم بيدّي المقال مصداقية.'
                : "{$external} رابط خارجي.",
            ['count' => $external],
            weight: 1);

        /* ---------------------------------------------------- the images - */

        $checks[] = self::check('cover', 'صورة المقال',
            $cover === '' ? 'fail' : ($coverAlt === '' ? 'warn' : 'pass'),
            match (true) {
                $cover === '' => 'من غير صورة، المقال بيتشارك على واتساب وفيسبوك من غير أي شكل.',
                $coverAlt === '' => 'الصورة موجودة بس من غير نص بديل (alt).',
                default => 'صورة + نص بديل.',
            },
            weight: 2);

        preg_match_all('/<img\b[^>]*>/i', $body, $imgs);
        $missingAlt = 0;
        foreach ($imgs[0] ?? [] as $img) {
            if (! preg_match('/\balt\s*=\s*["\'][^"\']+["\']/i', $img)) {
                $missingAlt++;
            }
        }
        $checks[] = self::check('image_alt', 'نص بديل لصور المقال',
            $missingAlt > 0 ? 'warn' : 'pass',
            $missingAlt > 0
                ? "{$missingAlt} صورة من غير alt — دي مشكلة وصول قبل ما تبقى مشكلة سيو."
                : 'كل الصور معاها نص بديل.',
            ['missing' => $missingAlt],
            weight: 2);

        /* --------------------------------------------------- the plumbing */

        $checks[] = self::check('slug', 'الرابط',
            match (true) {
                $slug === '' => 'fail',
                mb_strlen($slug) > 75 => 'warn',
                default => 'pass',
            },
            match (true) {
                $slug === '' => 'مفيش رابط.',
                mb_strlen($slug) > 75 => 'الرابط طويل — قصّره لأهم 3–5 كلمات.',
                default => 'الرابط مظبوط.',
            },
            weight: 1);

        $checks[] = self::check('excerpt', 'المقتطف',
            $excerpt === '' ? 'warn' : 'pass',
            $excerpt === ''
                ? 'المقتطف هو اللي بيظهر في قايمة المدوّنة.'
                : 'موجود.',
            weight: 1);

        if (! empty($post['noindex'])) {
            $checks[] = self::check('noindex', 'مستبعد من جوجل', 'fail',
                'المقال متعلّم noindex — مش هيظهر في البحث خالص.', weight: 5);
        }

        return [
            'score' => self::score($checks),
            'words' => $words,
            'reading_minutes' => max(1, (int) ceil($words / 180)),
            'checks' => $checks,
            'preview' => [
                'title' => $title !== '' ? $title : $rawTitle,
                'description' => $desc,
                'url' => $slug,
            ],
        ];
    }

    private static function check(string $id, string $label, string $status, string $hint, array $data = [], int $weight = 1): array
    {
        return compact('id', 'label', 'status', 'hint', 'data', 'weight');
    }

    /** A warn is worth half a pass — a page of warnings should not read as green. */
    private static function score(array $checks): int
    {
        $max = 0;
        $got = 0;
        foreach ($checks as $c) {
            $max += $c['weight'];
            $got += match ($c['status']) {
                'pass' => $c['weight'],
                'warn' => $c['weight'] * 0.5,
                default => 0,
            };
        }

        return $max === 0 ? 0 : (int) round($got / $max * 100);
    }
}
