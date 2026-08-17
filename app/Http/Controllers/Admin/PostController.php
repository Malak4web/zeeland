<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\Arabic;
use App\Support\SeoAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with(['category', 'author'])
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('category'), fn ($q, $c) => $q->where('category_id', $c))
            ->latest('updated_at')
            ->paginate(15)->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('sort')->get(),
            'counts' => Post::select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function create()
    {
        $post = new Post(['status' => 'draft', 'published_at' => now()]);

        return view('admin.posts.form', $this->formData($post));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tags = $this->resolveTags($request);

        $post = DB::transaction(function () use ($data, $tags) {
            $post = Post::create($data + ['author_id' => auth()->id()]);
            $post->tags()->sync($tags);

            return $post;
        });

        Activity::log('created', "كتب مقال «{$post->title}»", $post);

        return redirect()->route('admin.posts.edit', $post)->with('ok', 'المقال اتحفظ.');
    }

    public function edit(Post $post)
    {
        $post->load('tags');

        return view('admin.posts.form', $this->formData($post));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request, $post);
        $tags = $this->resolveTags($request);

        DB::transaction(function () use ($post, $data, $tags) {
            $post->update($data);
            $post->tags()->sync($tags);
        });

        Activity::log('updated', "عدّل مقال «{$post->title}»", $post);

        return redirect()->route('admin.posts.edit', $post)->with('ok', 'اتحدّث.');
    }

    public function destroy(Post $post)
    {
        $title = $post->title;
        $post->delete();
        Activity::log('deleted', "مسح مقال «{$title}»", $post);

        return redirect()->route('admin.posts.index')->with('ok', 'اتمسح.');
    }

    /**
     * The live assistant. Same analyzer the save path uses — no second copy.
     *
     * The slug is derived here too, not in the browser: an Arabic slug needs
     * exact harakat/tatweel handling, and a JavaScript reimplementation of that
     * is a silent-corruption bug waiting to happen.
     */
    public function seoCheck(Request $request): JsonResponse
    {
        // Cast: Laravel's ConvertEmptyStringsToNull turns a blank field into
        // null, so the fallback on an untouched new post is null, not ''.
        $slug = Arabic::slug((string) ($request->input('slug') ?: $request->input('title')));

        $result = SeoAnalyzer::analyze([
            'title' => $request->input('title'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'slug' => $slug,
            'excerpt' => $request->input('excerpt'),
            'body' => $request->input('body'),
            'focus_keyword' => $request->input('focus_keyword'),
            'cover_image' => $request->input('cover_image'),
            'cover_alt' => $request->input('cover_alt'),
            'noindex' => $request->boolean('noindex'),
        ]);

        $result['suggested_slug'] = $slug;

        return response()->json($result);
    }

    /** Editor image upload → public/uploads/YYYY/MM. */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif,gif', 'max:4096'],
        ], [
            'file.image' => 'الملف لازم يكون صورة.',
            'file.max' => 'أقصى حجم 4 ميجا.',
        ]);

        $dir = 'uploads/'.date('Y/m');
        $name = date('YmdHis').'-'.bin2hex(random_bytes(3)).'.'.$request->file('file')->extension();

        $request->file('file')->move(public_path($dir), $name);

        return response()->json(['ok' => true, 'path' => '/'.$dir.'/'.$name]);
    }

    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::orderBy('sort')->orderBy('name')->get(),
            'allTags' => Tag::orderBy('name')->get(),
            'seo' => SeoAnalyzer::analyze([
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
            ]),
        ];
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('posts', 'slug')->ignore($post)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:200000'],
            'cover_image' => ['nullable', 'string', 'max:300'],
            'cover_alt' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'string', 'max:300'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
        ], [
            'title.required' => 'العنوان مطلوب.',
            'slug.unique' => 'الرابط ده مستخدم في مقال تاني.',
            'canonical_url.url' => 'الرابط الأساسي لازم يكون URL كامل.',
        ]);

        // A slug is mandatory on save, so this is where the last-resort value
        // lives — never inside the slug helper itself.
        $data['slug'] = Arabic::slug($data['slug'] ?: $data['title']) ?: 'post-'.now()->format('Ymd-His');
        $data['slug'] = $this->uniqueSlug($data['slug'], $post);

        $data['body'] = $this->sanitize($data['body'] ?? '');
        $data['noindex'] = $request->boolean('noindex');
        $data['nofollow'] = $request->boolean('nofollow');
        // reading_minutes and seo_score are computed by Post::booted() on save.

        // Publishing with no date set means "now"; scheduling keeps the date.
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        if ($data['status'] === 'draft') {
            $data['published_at'] = $data['published_at'] ?: null;
        }

        $data['seo_score'] = SeoAnalyzer::analyze($data + [
            'cover_image' => $data['cover_image'] ?? null,
            'cover_alt' => $data['cover_alt'] ?? null,
        ])['score'];

        return $data;
    }

    private function uniqueSlug(string $slug, ?Post $post): string
    {
        $base = $slug;
        $i = 2;
        while (Post::withTrashed()->where('slug', $slug)->when($post, fn ($q) => $q->whereKeyNot($post->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * Editors are trusted, but a pasted <script> from a Word document is not.
     * Strip the executable surface and keep the formatting tags.
     */
    private function sanitize(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input)\b[^>]*/?>#is', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>]*\2/is', '$1="#"', $html);

        return trim((string) $html);
    }

    /** @return array<int, int> tag ids, creating any that are new */
    private function resolveTags(Request $request): array
    {
        $names = collect(explode(',', (string) $request->input('tags')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->take(12);

        return $names->map(function (string $name) {
            return Tag::firstOrCreate(
                ['slug' => Arabic::slug($name)],
                ['name' => $name]
            )->id;
        })->all();
    }
}
