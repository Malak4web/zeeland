<?php

namespace App\Models;

use App\Support\SeoAnalyzer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft' => 'مسودة',
        'scheduled' => 'مجدول',
        'published' => 'منشور',
    ];

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'body',
        'cover_image', 'cover_alt', 'status', 'published_at',
        'meta_title', 'meta_description', 'og_image', 'canonical_url',
        'focus_keyword', 'noindex', 'nofollow', 'reading_minutes', 'seo_score',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'noindex' => 'boolean',
            'nofollow' => 'boolean',
        ];
    }

    /**
     * Reading time and SEO score are derived from the post's own fields, so
     * they are computed on save rather than by whoever happens to be writing.
     * A seeder, a console command and the editor all get the same numbers.
     */
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            $post->reading_minutes = self::estimateReadingMinutes($post->body);

            $post->seo_score = SeoAnalyzer::analyze([
                'title' => $post->title,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'focus_keyword' => $post->focus_keyword,
                'cover_image' => $post->cover_image,
                'cover_alt' => $post->cover_alt,
                'noindex' => $post->noindex,
            ])['score'];
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Live on the site only when published AND the publish moment has passed —
     * that single condition is what makes scheduling work with no cron job.
     */
    public function scopeLive(Builder $q): Builder
    {
        return $q->where('status', 'published')->where('published_at', '<=', now());
    }

    public function isLive(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->lte(now());
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function url(): string
    {
        return route('blog.show', $this->slug);
    }

    /* ---------------------------------------------------------------- SEO */

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->meta_description
            ?: Str::limit(trim(strip_tags((string) ($this->excerpt ?: $this->body))), 155, '');
    }

    public function shareImage(): ?string
    {
        return $this->og_image ?: $this->cover_image;
    }

    /** Plain text of the body — what word counts and keyword checks read. */
    public function plainBody(): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $this->body)));
    }

    public function wordCount(): int
    {
        $text = $this->plainBody();

        return $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    }

    public static function estimateReadingMinutes(?string $body): int
    {
        $words = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $body)));
        $count = $words === '' ? 0 : count(preg_split('/\s+/u', $words));

        // ~180 Arabic words a minute, floor of one.
        return max(1, (int) ceil($count / 180));
    }
}
