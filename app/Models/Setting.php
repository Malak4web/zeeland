<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'group'];

    public const CACHE_KEY = 'zl.settings';

    /** Every setting the app reads, with the value it falls back to. */
    public static function defaults(): array
    {
        return [
            // identity
            'site_name' => 'زيلاند',
            'site_name_en' => 'Zeeland',
            'site_tagline' => 'بطاطس نص مقلية مجمّدة · صنف سنتانا',
            'site_url' => rtrim((string) config('app.url'), '/'),

            // default SEO — used whenever a page does not override it
            'meta_title' => 'زيلاند — بطاطس نص مقلية مجمّدة، صنف سنتانا',
            'meta_description' => 'بطاطس زيلاند نص مقلية مجمّدة، قطع مستقيم، صنف سنتانا مختار بالتحليل: سكر منخفض وصلابة عالية. عبوة 2.5 كجم للمطاعم والكافيهات والموزّعين في مصر.',
            'og_image' => '/img/brand-kitchen.jpg',
            'twitter_handle' => '',

            // search console / analytics
            'google_site_verification' => '',
            'google_analytics_id' => '',
            'meta_pixel_id' => '',
            'robots_extra' => '',

            // contact — the landing page and the blog both read these
            'phone' => '+20 100 123 4657',
            'whatsapp' => '201001234657',
            'email' => 'info@zeeland-foods.com',
            'address' => 'مصر',
            'hours' => 'السبت – الخميس · 9 ص – 6 م',

            // commerce
            'currency' => 'ج.م',
            'default_pack_price' => '0',

            // blog
            'blog_title' => 'مدوّنة زيلاند',
            'blog_description' => 'مقالات عن البطاطس المجمّدة، الأصناف، القلي، والتشغيل في المطابخ التجارية.',
            'posts_per_page' => '9',
        ];
    }

    /** All settings, merged over the defaults, cached until something writes. */
    public static function all_(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = static::query()->pluck('value', 'key')->all();

            return array_merge(static::defaults(), array_filter(
                $stored,
                fn ($v) => $v !== null && $v !== ''
            ));
        });
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        return self::all_()[$key] ?? $fallback ?? (self::defaults()[$key] ?? null);
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function putMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}
