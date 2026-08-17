<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Support\Seo;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * One flat sitemap. Under ~50k URLs (which this will be for years) a single
     * file indexes faster than an index-of-sitemaps and is easier to eyeball.
     */
    public function sitemap(): Response
    {
        $origin = Seo::origin();
        $urls = [];

        $newest = Post::query()->live()->max('updated_at');

        $urls[] = ['loc' => $origin.'/', 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $newest];
        $urls[] = ['loc' => Seo::abs(route('blog.index')), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => $newest];

        foreach (Category::query()->whereHas('posts', fn ($q) => $q->live())->get() as $category) {
            $urls[] = [
                'loc' => Seo::abs($category->url()),
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at,
            ];
        }

        Post::query()->live()->orderByDesc('published_at')->chunk(200, function ($posts) use (&$urls, $origin) {
            foreach ($posts as $post) {
                if ($post->noindex) {
                    continue;   // never advertise a page we told Google to skip
                }
                $urls[] = [
                    'loc' => Seo::abs($post->url()),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => $post->updated_at,
                    'image' => $post->cover_image ? $origin.'/'.ltrim($post->cover_image, '/') : null,
                ];
            }
        });

        foreach (Tag::query()->whereHas('posts', fn ($q) => $q->live())->get() as $tag) {
            $urls[] = ['loc' => Seo::abs($tag->url()), 'priority' => '0.4', 'changefreq' => 'monthly', 'lastmod' => $tag->updated_at];
        }

        return response()
            ->view('site.seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /api/',
            '',
            'Sitemap: '.Seo::abs(route('sitemap')),
        ];

        if ($extra = trim((string) Setting::get('robots_extra'))) {
            array_splice($lines, 3, 0, preg_split('/\r\n|\r|\n/', $extra));
        }

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function feed(): Response
    {
        $posts = Post::query()->live()->with('author')->latest('published_at')->take(20)->get();

        return response()
            ->view('site.seo.feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
