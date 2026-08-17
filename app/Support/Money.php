<?php

namespace App\Support;

use App\Models\Setting;

final class Money
{
    /** 12345.5 → "12,345.50" — Latin digits, because the UI sets them in mono. */
    public static function format(float|int|string|null $amount, bool $withCurrency = false): string
    {
        $value = number_format((float) $amount, 2, '.', ',');

        return $withCurrency ? $value.' '.Setting::get('currency', 'ج.م') : $value;
    }

    /** Drops the ".00" when a figure is whole — reads better in dense tables. */
    public static function short(float|int|string|null $amount): string
    {
        $amount = (float) $amount;

        return number_format($amount, fmod($amount, 1) === 0.0 ? 0 : 2, '.', ',');
    }

    public static function currency(): string
    {
        return Setting::get('currency', 'ج.م');
    }
}
