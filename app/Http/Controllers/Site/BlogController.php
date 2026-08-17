<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    private function perPage(): int
    {
        return max(3, (int) Setting::get('posts_per_page', 9));
    }

    private function base(): Builder
    {
        return Post::query()->live()->with(['category', 'author'])->latest('published_at');
    }

    public function index(Request $request)
    {
        $posts = $this->base()->paginate($this->perPage())->withQueryString();
        $categories = Category::query()->withCount(['posts' => fn ($q) => $q->live()])->orderBy('sort')->get();

        $page = max(1, (int) $request->query('page', 1));
        $title = (string) Setting::get('blog_title');

        $seo = Seo::make()
            ->title($page > 1 ? "{$title} — صفحة {$page}" : $title)
            ->description(Setting::get('blog_description'))
            ->canonical(route('blog.index').($page > 1 ? "?page={$page}" : ''))
            ->breadcrumbs([
                ['name' => 'الرئيسية', 'url' => route('home')],
                ['name' => $title, 'url' => route('blog.index')],
            ])
            ->schema([
                '@type' => 'Blog',
                '@id' => route('blog.index').'#blog',
                'url' => route('blog.index'),
                'name' => $title,
                'description' => Setting::get('blog_description'),
                'inLanguage' => 'ar-EG',
                'publisher' => ['@id' => Seo::origin().'/#organization'],
            ]);

        $featured = $page === 1 ? $posts->first() : null;

        return view('site.blog.index', [
            'seo' => $seo,
            'posts' => $posts,
            'featured' => $featured,
            'categories' => $categories,
            'heading' => $title,
            'intro' => Setting::get('blog_description'),
        ]);
    }

    public function category(Category $category)
    {
        $posts = $this->base()->where('category_id', $category->id)
            ->paginate($this->perPage())->withQueryString();
        $categories = Category::query()->withCount(['posts' => fn ($q) => $q->live()])->orderBy('sort')->get();

        $seo = Seo::make()
            ->title($category->meta_title ?: $category->name)
            ->description($category->meta_description ?: $category->description ?: Setting::get('blog_description'))
            ->canonical($category->url())
            ->breadcrumbs([
                ['name' => 'الرئيسية', 'url' => route('home')],
                ['name' => Setting::get('blog_title'), 'url' => route('blog.index')],
                ['name' => $category->name, 'url' => $category->url()],
            ]);

        return view('site.blog.index', [
            'seo' => $seo,
            'posts' => $posts,
            'featured' => null,
            'categories' => $categories,
            'heading' => $category->name,
            'intro' => $category->description,
            'activeCategory' => $category->id,
        ]);
    }

    public function tag(Tag $tag)
    {
        $posts = $this->base()
            ->whereHas('tags', fn (Builder $q) => $q->where('tags.id', $tag->id))
            ->paginate($this->perPage())->withQueryString();
        $categories = Category::query()->withCount(['posts' => fn ($q) => $q->live()])->orderBy('sort')->get();

        $seo = Seo::make()
            ->title("مقالات عن {$tag->name}")
            ->description("كل مقالات زيلاند المرتبطة بـ {$tag->name}.")
            ->canonical($tag->url())
            ->breadcrumbs([
                ['name' => 'الرئيسية', 'url' => route('home')],
                ['name' => Setting::get('blog_title'), 'url' => route('blog.index')],
                ['name' => $tag->name, 'url' => $tag->url()],
            ]);

        return view('site.blog.index', [
            'seo' => $seo,
            'posts' => $posts,
            'featured' => null,
            'categories' => $categories,
            'heading' => "#{$tag->name}",
            'intro' => null,
        ]);
    }

    public function show(Post $post)
    {
        // A draft stays reachable for a logged-in editor previewing their work.
        abort_unless($post->isLive() || auth()->check(), 404);

        $post->loadMissing(['category', 'author', 'tags']);
        $post->incrementQuietly('views');

        $related = Post::query()->live()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')->take(3)->get();

        if ($related->count() < 3) {
            $related = $related->concat(
                Post::query()->live()
                    ->whereNotIn('id', $related->pluck('id')->push($post->id))
                    ->latest('published_at')->take(3 - $related->count())->get()
            );
        }

        $seo = Seo::make()
            ->title($post->metaTitle())
            ->description($post->metaDescription())
            ->canonical($post->canonical_url ?: $post->url())
            ->image($post->shareImage())
            ->robots($post->noindex, $post->nofollow)
            ->article(
                $post->published_at?->toAtomString(),
                $post->updated_at?->toAtomString(),
                $post->author?->name,
            )
            ->breadcrumbs(array_values(array_filter([
                ['name' => 'الرئيسية', 'url' => route('home')],
                ['name' => Setting::get('blog_title'), 'url' => route('blog.index')],
                $post->category ? ['name' => $post->category->name, 'url' => $post->category->url()] : null,
                ['name' => $post->title, 'url' => $post->url()],
            ])))
            ->schema(array_filter([
                '@type' => 'BlogPosting',
                '@id' => $post->url().'#article',
                'headline' => $post->title,
                'description' => $post->metaDescription(),
                'image' => $post->shareImage() ? Seo::origin().'/'.ltrim($post->shareImage(), '/') : null,
                'datePublished' => $post->published_at?->toAtomString(),
                'dateModified' => $post->updated_at?->toAtomString(),
                'inLanguage' => 'ar-EG',
                'wordCount' => $post->wordCount(),
                'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : ['@id' => Seo::origin().'/#organization'],
                'publisher' => ['@id' => Seo::origin().'/#organization'],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $post->url()],
                'articleSection' => $post->category?->name,
                'keywords' => $post->tags->pluck('name')->implode(', ') ?: null,
            ]));

        return view('site.blog.show', compact('seo', 'post', 'related'));
    }
}
