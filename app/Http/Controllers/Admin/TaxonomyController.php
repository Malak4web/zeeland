<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Tag;
use App\Support\Arabic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxonomyController extends Controller
{
    public function index()
    {
        return view('admin.taxonomy.index', [
            'categories' => Category::withCount('posts')->orderBy('sort')->orderBy('name')->get(),
            'tags' => Tag::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $category = Category::create($this->categoryData($request));
        Activity::log('created', "أضاف قسم «{$category->name}»", $category);

        return back()->with('ok', 'القسم اتضاف.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $category->update($this->categoryData($request, $category));
        Activity::log('updated', "عدّل القسم «{$category->name}»", $category);

        return back()->with('ok', 'اتحدّث.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->withErrors(['category' => 'فيه مقالات في القسم ده. انقلها الأول.']);
        }

        $name = $category->name;
        $category->delete();
        Activity::log('deleted', "مسح القسم «{$name}»", $category);

        return back()->with('ok', 'اتمسح.');
    }

    public function storeTag(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);
        $tag = Tag::firstOrCreate(['slug' => Arabic::slug($data['name'])], ['name' => $data['name']]);

        return back()->with('ok', "الوسم «{$tag->name}» جاهز.");
    }

    public function destroyTag(Tag $tag)
    {
        $name = $tag->name;
        $tag->delete();
        Activity::log('deleted', "مسح الوسم «{$name}»", $tag);

        return back()->with('ok', 'اتمسح.');
    }

    private function categoryData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'name.required' => 'اسم القسم مطلوب.',
            'slug.unique' => 'الرابط ده مستخدم.',
        ]);

        $data['slug'] = Arabic::slug($data['slug'] ?: $data['name']);
        $data['sort'] = (int) ($data['sort'] ?? 0);

        return $data;
    }
}
