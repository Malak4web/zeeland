<?php

namespace App\Support;

/**
 * The three Arabic text jobs this app keeps getting wrong if left to defaults:
 * slugs (Str::slug deletes Arabic outright), phone digits (users paste
 * Arabic-Indic numerals), and search normalisation (أ/ا, ة/ه, ى/ي).
 */
final class Arabic
{
    private const AR_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    private const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /** Arabic-Indic and Persian numerals → 0-9. */
    public static function digits(string $value): string
    {
        return str_replace(
            array_merge(self::AR_DIGITS, self::FA_DIGITS),
            array_merge(range(0, 9), range(0, 9)),
            $value
        );
    }

    /**
     * A slug that keeps Arabic letters instead of erasing them.
     * Google indexes percent-encoded Arabic paths fine and the words in the URL
     * are a real ranking and click-through signal in Arabic SERPs.
     */
    public static function slug(string $value): string
    {
        $value = self::digits(trim($value));

        // strip harakat + tatweel, they never belong in a URL
        $value = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value);
        $value = trim((string) $value, '-');

        // Empty in, empty out — the caller decides whether a blank slug is a
        // problem. Returning a placeholder here once put the literal word
        // "post" in the editor's live URL preview.
        return mb_substr($value, 0, 90);
    }

    /** Fold the letter variants Egyptians type interchangeably. */
    public static function fold(string $value): string
    {
        $value = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0640}]/u', '', $value);

        return str_replace(
            ['أ', 'إ', 'آ', 'ة', 'ى', 'ؤ', 'ئ'],
            ['ا', 'ا', 'ا', 'ه', 'ي', 'و', 'ي'],
            mb_strtolower($value, 'UTF-8')
        );
    }

    /** Egyptian mobile → wa.me digits (01xxxxxxxxx → 201xxxxxxxxx). */
    public static function whatsappDigits(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', self::digits((string) $phone));
        if (str_starts_with($digits, '0')) {
            $digits = '20'.substr($digits, 1);
        }

        return $digits;
    }
}
