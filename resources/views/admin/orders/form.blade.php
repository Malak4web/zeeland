@extends('layouts.admin')

@section('title', $order->exists ? 'تعديل '.$order->code : 'أوردر جديد')
@section('back', $order->exists ? route('admin.orders.show', $order) : route('admin.orders.index'))

@section('content')
    @php
        $lines = old('items') ?: ($order->exists
            ? $order->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'name' => $i->name,
                'unit' => $i->unit,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->all()
            : [['product_id' => $products->first()?->id, 'name' => $products->first()?->name, 'unit' => $products->first()?->unit ?? 'شيكارة', 'quantity' => 1, 'unit_price' => (float) ($products->first()?->price ?? 0)]]);
    @endphp

    <form method="POST"
          action="{{ $order->exists ? route('admin.orders.update', $order) : route('admin.orders.store') }}"
          data-order-editor
          data-products="{{ $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'unit' => $p->unit, 'price' => (float) $p->price])->toJson() }}"
          data-customers="{{ $customers->map(fn ($c) => ['id' => $c->id, 'price_per_pack' => $c->price_per_pack ? (float) $c->price_per_pack : null, 'address' => $c->address])->toJson() }}"
          class="grid gap-4 lg:grid-cols-12">
        @csrf
        @if($order->exists) @method('PATCH') @endif

        <div class="flex flex-col gap-4 lg:col-span-8">
            <section class="panel p-5" aria-labelledby="h-h">
                <h2 id="h-h" class="text-sm font-semibold text-cream">بيانات الأوردر</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="customer_id" class="label">العميل <span class="text-flame-ink">*</span></label>
                        <input type="search" data-select-filter="#customer_id" placeholder="اكتب جزء من الاسم عشان تفلتر…" class="field mb-2 text-xs" aria-label="فلترة العملاء">
                        <select id="customer_id" name="customer_id" data-customer-select required class="field"
                                @if($errors->has('customer_id')) aria-invalid="true" @endif>
                            <option value="">اختار عميل…</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $order->customer_id) == $c->id)>{{ $c->name }} — {{ $c->code }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<p class="error">{{ $message }}</p>@enderror
                        @if($customers->isEmpty())
                            <p class="hint">مفيش عملاء لسه — <a href="{{ route('admin.customers.create') }}" class="text-flame-ink-hi">سجّل عميل الأول</a>.</p>
                        @endif
                    </div>

                    <div>
                        <label for="order_date" class="label">تاريخ الأوردر <span class="text-flame-ink">*</span></label>
                        <input id="order_date" name="order_date" type="date" required class="field num"
                               value="{{ old('order_date', $order->order_date?->toDateString() ?? now()->toDateString()) }}">
                    </div>

                    <div>
                        <label for="delivery_date" class="label">تاريخ التسليم</label>
                        <input id="delivery_date" name="delivery_date" type="date" class="field num"
                               value="{{ old('delivery_date', $order->delivery_date?->toDateString()) }}">
                    </div>

                    <div>
                        <label for="status" class="label">الحالة</label>
                        <select id="status" name="status" class="field">
                            @foreach(\App\Models\Order::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $order->status ?: 'confirmed') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="hint">المسودة والملغي مابيدخلوش في حساب العميل.</p>
                    </div>

                    <div>
                        <label for="delivery_address" class="label">عنوان التسليم</label>
                        <input id="delivery_address" name="delivery_address" data-delivery-address class="field"
                               value="{{ old('delivery_address', $order->delivery_address) }}" placeholder="بيتملى من ملف العميل">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">ملاحظات</label>
                        <textarea id="notes" name="notes" rows="2" class="field">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>
            </section>

            {{-- The line editor. Totals move as you type; the server recomputes
                 them from the saved rows anyway. --}}
            <section class="panel" aria-labelledby="l-h">
                <div class="panel-head">
                    <h2 id="l-h" class="panel-title">الأصناف</h2>
                    <button type="button" data-add-line class="btn btn-ghost btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        سطر
                    </button>
                </div>

                <div data-lines class="divide-y divide-navy-2">
                    @foreach($lines as $i => $line)
                        <div data-line class="p-4">
                            <div class="flex items-center justify-between gap-3 pb-3">
                                <span class="num text-2xs text-cream-3">سطر <span data-line-no>{{ $i + 1 }}</span></span>
                                <button type="button" data-remove-line class="btn btn-ghost btn-sm btn-icon text-cream-3" aria-label="شيل السطر ده">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="label">الصنف</label>
                                    <select name="items[{{ $i }}][product_id]" data-product class="field" aria-label="اختيار الصنف">
                                        <option value="">صنف حر</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" @selected(($line['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="items[{{ $i }}][name]" data-name required class="field mt-2 text-xs"
                                           value="{{ $line['name'] ?? '' }}" placeholder="الاسم اللي هيتكتب في الفاتورة" aria-label="اسم الصنف">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="label">الكمية</label>
                                    <input type="number" name="items[{{ $i }}][quantity]" data-qty step="0.01" min="0.01" required dir="ltr"
                                           value="{{ $line['quantity'] ?? 1 }}" class="field num text-start" aria-label="الكمية">
                                    <input type="hidden" name="items[{{ $i }}][unit]" data-unit value="{{ $line['unit'] ?? 'شيكارة' }}">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="label">السعر</label>
                                    <input type="number" name="items[{{ $i }}][unit_price]" data-price step="0.01" min="0" required dir="ltr"
                                           value="{{ $line['unit_price'] ?? 0 }}" class="field num text-start" aria-label="سعر الوحدة">
                                </div>

                                <div class="sm:col-span-3">
                                    <label class="label">الإجمالي</label>
                                    <p class="num flex min-h-[2.75rem] items-center justify-end rounded-lg border border-navy-2 bg-navy/40 px-3 text-sm font-semibold text-cream"
                                       data-line-total>0.00</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <template data-line-template>
                    <div data-line class="p-4">
                        <div class="flex items-center justify-between gap-3 pb-3">
                            <span class="num text-2xs text-cream-3">سطر <span data-line-no></span></span>
                            <button type="button" data-remove-line class="btn btn-ghost btn-sm btn-icon text-cream-3" aria-label="شيل السطر ده">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                            </button>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <label class="label">الصنف</label>
                                <select name="items[][product_id]" data-product class="field" aria-label="اختيار الصنف">
                                    <option value="">صنف حر</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="items[][name]" data-name required class="field mt-2 text-xs" placeholder="الاسم اللي هيتكتب في الفاتورة" aria-label="اسم الصنف">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">الكمية</label>
                                <input type="number" name="items[][quantity]" data-qty step="0.01" min="0.01" required dir="ltr" value="1" class="field num text-start" aria-label="الكمية">
                                <input type="hidden" name="items[][unit]" data-unit value="شيكارة">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">السعر</label>
                                <input type="number" name="items[][unit_price]" data-price step="0.01" min="0" required dir="ltr" value="0" class="field num text-start" aria-label="سعر الوحدة">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="label">الإجمالي</label>
                                <p class="num flex min-h-[2.75rem] items-center justify-end rounded-lg border border-navy-2 bg-navy/40 px-3 text-sm font-semibold text-cream" data-line-total>0.00</p>
                            </div>
                        </div>
                    </div>
                </template>
            </section>
        </div>

        {{-- Totals stay pinned beside the lines: the number you are deciding on
             should never be scrolled off. --}}
        <div class="lg:col-span-4">
            <div class="flex flex-col gap-4 lg:sticky lg:top-24">
                <section class="panel p-5" aria-labelledby="t-h">
                    <h2 id="t-h" class="text-sm font-semibold text-cream">الحساب</h2>

                    <dl class="mt-4 flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-cream-3">مجموع الأصناف</dt>
                            <dd class="num text-sm text-cream" data-subtotal>0.00</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-cream-3">إجمالي الشكاير</dt>
                            <dd class="num text-sm text-cream-2" data-packs>0</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label for="discount" class="text-xs text-cream-3">خصم</label>
                            <input id="discount" name="discount" data-discount type="number" step="0.01" min="0" dir="ltr"
                                   value="{{ old('discount', $order->discount ?? 0) }}" class="field num max-w-[8rem] text-start">
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <label for="shipping" class="text-xs text-cream-3">شحن</label>
                            <input id="shipping" name="shipping" data-shipping type="number" step="0.01" min="0" dir="ltr"
                                   value="{{ old('shipping', $order->shipping ?? 0) }}" class="field num max-w-[8rem] text-start">
                        </div>

                        <div class="mt-2 flex items-baseline justify-between gap-3 border-t border-navy-2 pt-4">
                            <dt class="text-sm text-cream">الإجمالي</dt>
                            <dd class="flex items-baseline gap-1.5">
                                <span class="num text-2xl font-semibold text-flame-ink" data-grand-total>0.00</span>
                                <span class="text-2xs text-cream-3">{{ $currency }}</span>
                            </dd>
                        </div>
                    </dl>
                </section>

                <div class="flex gap-2.5">
                    <button type="submit" class="btn btn-primary flex-1">{{ $order->exists ? 'احفظ' : 'سجّل الأوردر' }}</button>
                    <a href="{{ $order->exists ? route('admin.orders.show', $order) : route('admin.orders.index') }}" class="btn btn-ghost">إلغاء</a>
                </div>
            </div>
        </div>
    </form>
@endsection
