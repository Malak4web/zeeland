<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products.index', [
            'products' => Product::withCount('items')->orderBy('sort')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request));
        Activity::log('created', "أضاف صنف {$product->name}", $product);

        return back()->with('ok', 'الصنف اتضاف.');
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));
        Activity::log('updated', "عدّل الصنف {$product->name}", $product);

        return back()->with('ok', 'اتحدّث.');
    }

    public function destroy(Product $product)
    {
        if ($product->items()->exists()) {
            return back()->withErrors(['product' => 'الصنف ده مستخدم في طلبات. وقّفه بدل ما تمسحه.']);
        }

        $name = $product->name;
        $product->delete();
        Activity::log('deleted', "مسح الصنف {$name}", $product);

        return back()->with('ok', 'اتمسح.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:160'],
            'variety' => ['nullable', 'string', 'max:60'],
            'cut' => ['nullable', 'string', 'max:60'],
            'pack_size_kg' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'sku.unique' => 'الكود ده مستخدم قبل كده.',
            'name.required' => 'اسم الصنف مطلوب.',
        ]) + ['is_active' => $request->boolean('is_active'), 'sort' => (int) $request->input('sort', 0)];
    }
}
