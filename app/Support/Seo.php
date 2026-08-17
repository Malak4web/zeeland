<?php

namespace App\Support;

use App\Models\Setting;

/**
 * One object per page carrying everything the <head> needs.
 *
 * Controllers describe the page; the layout renders it. That split is what
 * stops a route from silently shipping without a canonical or an og:image.
 */
final class Seo
{
    public string $title = '';

    public string $description = '';

    public ?string $canonical = null;

    public ?string $image = null;

    public string $type = 'website';

    public bool $noindex = false;

    public bool $nofollow = false;

    public ?string $publishedAt = null;

    public ?string $modifiedAt = null;

    public ?string $author = null;

    public array $schema = [];

    public array $breadcrumbs = [];

    public static function make(): self
    {
        $seo = new self;
        $seo->title = (string) Setting::get('meta_title');
        $seo->description = (string) Setting::get('meta_description');
        $seo->image = (string) Setting::get('og_image');

        return $seo;
    }

    public function title(string $value, bool $suffix = true): self
    {
        $name = Setting::get('site_name', 'زيلاند');
        $this->title = $suffix && ! str_contains($value, (string) $name)
            ? "{$value} — {$name}"
            : $value;

        return $this;
    }

    public function description(?string $value): self
    {
        if ($value) {
            $this->description = trim(preg_replace('/\s+/u', ' ', strip_tags($value)));
        }

        return $this;
    }

    public function canonical(?string $url): self
    {
        $this->canonical = $url ? self::abs($url) : null;

        return $this;
    }

    public function image(?string $url): self
    {
        if ($url) {
            $this->image = $url;
        }

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function robots(bool $noindex = false, bool $nofollow = false): self
    {
        $this->noindex = $noindex;
        $this->nofollow = $nofollow;

        return $this;
    }

    public function article(?string $published, ?string $modified, ?string $author): self
    {
        $this->type = 'article';
        $this->publishedAt = $published;
        $this->modifiedAt = $modified;
        $this->author = $author;

        return $this;
    }

    public function schema(array $node): self
    {
        $this->schema[] = $node;

        return $this;
    }

    /** @param array<int, array{name: string, url: string}> $items */
    public function breadcrumbs(array $items): self
    {
        $this->breadcrumbs = $items;

        return $this;
    }

    public function absoluteImage(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http')
            ? $this->image
            : self::origin().'/'.ltrim($this->image, '/');
    }

    public function robotsContent(): string
    {
        $parts = [$this->noindex ? 'noindex' : 'index', $this->nofollow ? 'nofollow' : 'follow'];
        if (! $this->noindex) {
            $parts[] = 'max-image-preview:large';
            $parts[] = 'max-snippet:-1';
        }

        return implode(', ', $parts);
    }

    /** The one place the public origin is decided. */
    public static function origin(): string
    {
        return rtrim((string) (Setting::get('site_url') ?: config('app.url')), '/');
    }

    /**
     * Force any generated URL onto the canonical origin.
     *
     * route() builds from the host that served the request, so hitting the app
     * on 127.0.0.1, on an IP, or through a staging hostname would otherwise
     * publish two spellings of the same page — the classic way a site splits
     * its own ranking. Everything Google reads goes through here.
     */
    public static function abs(string $url): string
    {
        $origin = self::origin();
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return $origin.$path.($query ? '?'.$query : '');
    }

    /** Everything that goes in the JSON-LD block, as one @graph. */
    public function graph(): array
    {
        $graph = $this->schema;

        if ($this->breadcrumbs) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_map(fn ($item, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ], $this->breadcrumbs, array_keys($this->breadcrumbs)),
            ];
        }

        $graph[] = self::organisation();

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }

    public static function organisation(): array
    {
        $origin = self::origin();

        return array_filter([
            '@type' => 'Organization',
            '@id' => $origin.'/#organization',
            'name' => Setting::get('site_name_en', 'Zeeland'),
            'alternateName' => Setting::get('site_name', 'زيلاند'),
            'url' => $origin.'/',
            'logo' => $origin.'/icon-512.png',
            'image' => $origin.'/img/brand-kitchen.jpg',
            'description' => Setting::get('meta_description'),
            'telephone' => Setting::get('phone'),
            'email' => Setting::get('email') ?: null,
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'EG',
                'addressLocality' => Setting::get('address'),
            ],
        ]);
    }
}
