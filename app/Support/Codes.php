<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Human-readable sequential document numbers (ZL-C-0007, ZL-2026-0142).
 *
 * The sequence is derived from the highest existing code rather than a counter
 * table, so a restored backup or a manually inserted row can never hand out a
 * number twice. Zero padding is what makes the string sort match the numeric
 * sort — do not shorten it.
 *
 * Call inside a transaction: the lockForUpdate is what stops two clerks saving
 * at the same instant from both taking ZL-2026-0143.
 */
final class Codes
{
    public static function next(string $table, string $prefix, int $pad = 4): string
    {
        $last = DB::table($table)
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->lockForUpdate()
            ->value('code');

        $n = $last ? (int) substr($last, strlen($prefix)) : 0;

        return $prefix.str_pad((string) ($n + 1), $pad, '0', STR_PAD_LEFT);
    }

    public static function customer(): string
    {
        return self::next('customers', 'ZL-C-');
    }

    public static function order(?int $year = null): string
    {
        return self::next('orders', 'ZL-'.($year ?: (int) date('Y')).'-');
    }

    public static function payment(?int $year = null): string
    {
        return self::next('payments', 'ZL-P-'.($year ?: (int) date('Y')).'-');
    }
}
