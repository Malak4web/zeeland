@extends('layouts.admin')

@section('title', 'الأصناف')
@section('subtitle', $products->count().' صنف')

@section('actions')
    @if(auth()->user()->can_('products.edit'))
        <button type="button" data-sheet-open="new-product" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">صنف</span>
        </button>
    @endif
@endsection

@section('content')
    @if($products->isEmpty())
        <x-empty title="مفيش أصناف" hint="الصنف هو اللي بيظهر في سطور الأوردر بسعره الافتراضي." />
    @else
        <ul class="flex flex-col gap-3">
            @foreach($products as $p)
                <li class="panel p-4">
                    <form method="POST" action="{{ route('admin.products.update', $p) }}" class="grid gap-3 lg:grid-cols-12 lg:items-end">
                        @csrf @method('PATCH')

                        <div class="lg:col-span-4">
                            <label for="name-{{ $p->id }}" class="label">الاسم</label>
                            <input id="name-{{ $p->id }}" name="name" value="{{ $p->name }}" required class="field">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="sku-{{ $p->id }}" class="label">الكود</label>
                            <input id="sku-{{ $p->id }}" name="sku" value="{{ $p->sku }}" required dir="ltr" class="field num text-start">
                        </div>
                        <div class="lg:col-span-1">
                            <label for="kg-{{ $p->id }}" class="label">كجم</label>
                            <input id="kg-{{ $p->id }}" name="pack_size_kg" type="number" step="0.01" min="0.01" value="{{ (float) $p->pack_size_kg }}" required dir="ltr" class="field num text-start">
                        </div>
                        <div class="lg:col-span-1">
                            <label for="unit-{{ $p->id }}" class="label">الوحدة</label>
                            <input id="unit-{{ $p->id }}" name="unit" value="{{ $p->unit }}" required class="field text-xs">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="price-{{ $p->id }}" class="label">السعر ({{ $currency }})</label>
                            <input id="price-{{ $p->id }}" name="price" type="number" step="0.01" min="0" value="{{ (float) $p->price }}" required dir="ltr" class="field num text-start">
                        </div>
                        <div class="flex items-center gap-2 lg:col-span-2">
                            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-xl border border-navy-2 px-3 py-2.5 text-xs text-cream-2">
                                <input type="checkbox" name="is_active" value="1" @checked($p->is_active) class="size-4 accent-flame">
                                نشط
                            </label>
                            <button type="submit" class="btn btn-solid btn-sm">احفظ</button>
                        </div>

                        <input type="hidden" name="variety" value="{{ $p->variety }}">
                        <input type="hidden" name="cut" value="{{ $p->cut }}">
                        <input type="hidden" name="sort" value="{{ $p->sort }}">
                    </form>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-navy-2 pt-3">
                        <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-2xs text-cream-3">
                            @if($p->variety)<span>صنف: {{ $p->variety }}</span>@endif
                            @if($p->cut)<span>{{ $p->cut }}</span>@endif
                            <span>مستخدم في <span class="num">{{ $p->items_count }}</span> سطر أوردر</span>
                        </p>
                        @if(auth()->user()->can_('products.edit') && $p->items_count === 0)
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}"
                                  data-confirm="هيتمسح صنف «{{ $p->name }}».">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">امسح</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection

@push('sheets')
    @if(auth()->user()->can_('products.edit'))
        <dialog class="sheet" data-sheet="new-product" aria-labelledby="pr-h">
            <div class="sheet-panel">
                <div class="sheet-grip" aria-hidden="true"></div>
                <h2 id="pr-h" class="text-base font-semibold text-cream">صنف جديد</h2>

                <form method="POST" action="{{ route('admin.products.store') }}" class="mt-5 flex flex-col gap-4">
                    @csrf
                    <div>
                        <label for="p-name" class="label">الاسم <span class="text-flame-ink">*</span></label>
                        <input id="p-name" name="name" required class="field" placeholder="زيلاند بطاطس نص مقلية — 2.5 كجم">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="p-sku" class="label">الكود <span class="text-flame-ink">*</span></label>
                            <input id="p-sku" name="sku" required dir="ltr" class="field num text-start" placeholder="ZL-SAN-2500">
                        </div>
                        <div>
                            <label for="p-price" class="label">السعر ({{ $currency }})</label>
                            <input id="p-price" name="price" type="number" step="0.01" min="0" value="0" required dir="ltr" class="field num text-start">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="p-kg" class="label">وزن العبوة (كجم)</label>
                            <input id="p-kg" name="pack_size_kg" type="number" step="0.01" min="0.01" value="2.5" required dir="ltr" class="field num text-start">
                        </div>
                        <div>
                            <label for="p-unit" class="label">الوحدة</label>
                            <input id="p-unit" name="unit" value="شيكارة" required class="field">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="p-variety" class="label">الصنف الزراعي</label>
                            <input id="p-variety" name="variety" class="field" placeholder="سنتانا">
                        </div>
                        <div>
                            <label for="p-cut" class="label">القص</label>
                            <input id="p-cut" name="cut" class="field" placeholder="قطع مستقيم">
                        </div>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-navy-2 p-3 text-sm text-cream-2">
                        <input type="checkbox" name="is_active" value="1" checked class="size-4 accent-flame">
                        نشط
                    </label>
                    <div class="flex gap-2.5 pt-1">
                        <button type="submit" class="btn btn-primary flex-1">أضف</button>
                        <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endpush
