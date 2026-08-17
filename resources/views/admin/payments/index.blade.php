@extends('layouts.admin')

@section('title', 'الدفعات')
@section('subtitle', $payments->total().' دفعة')

@section('actions')
    @if(auth()->user()->can_('payments.edit'))
        <button type="button" data-sheet-open="new-payment" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">دفعة</span>
        </button>
    @endif
@endsection

@section('content')
    <div class="panel p-4">
        <p class="text-2xs text-cream-3">إجمالي التحصيل في الفترة المعروضة</p>
        <p class="num mt-1.5 text-2xl font-semibold text-good">{{ \App\Support\Money::format($rangeTotal) }} <span class="text-2xs font-normal text-cream-3">{{ $currency }}</span></p>

        @if($byMethod->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-x-5 gap-y-2 border-t border-navy-2 pt-3">
                @foreach(\App\Models\Payment::METHODS as $key => $label)
                    @if(($byMethod[$key] ?? 0) > 0)
                        <li class="text-2xs">
                            <span class="text-cream-3">{{ $label }}</span>
                            <span class="num ms-1.5 text-cream">{{ \App\Support\Money::short($byMethod[$key]) }}</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>

    <form method="GET" data-auto-filter class="mt-4 flex flex-wrap items-end gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="كود، مرجع، عميل…" class="field max-w-[16rem]" aria-label="بحث في الدفعات">
        <select name="customer" class="field max-w-[13rem]" aria-label="فلترة بالعميل">
            <option value="">كل العملاء</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(request('customer') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="method" class="field max-w-[9rem]" aria-label="فلترة بطريقة الدفع">
            <option value="">كل الطرق</option>
            @foreach(\App\Models\Payment::METHODS as $key => $label)
                <option value="{{ $key }}" @selected(request('method') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="field num max-w-[10rem]" aria-label="من تاريخ">
        <input type="date" name="to" value="{{ request('to') }}" class="field num max-w-[10rem]" aria-label="إلى تاريخ">
        @if(request()->hasAny(['q','customer','method','from','to']))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost btn-sm">مسح</a>
        @endif
    </form>

    @if($payments->isEmpty())
        <x-empty class="mt-6" title="مفيش دفعات في النطاق ده"
                 hint="كل دفعة بتتسجّل هنا بتقلّل رصيد العميل فورًا في كشف حسابه." />
    @else
        <ul class="mt-4 flex flex-col gap-2.5 lg:hidden">
            @foreach($payments as $p)
                <li class="panel p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.customers.show', $p->customer_id) }}" class="block truncate py-1 text-sm font-medium text-cream">{{ $p->customer?->name }}</a>
                            <p class="num mt-0.5 text-2xs text-cream-3">{{ $p->code }} · {{ $p->paid_at->format('Y-m-d') }}</p>
                        </div>
                        <p class="num shrink-0 text-base font-semibold text-good">{{ \App\Support\Money::short($p->amount) }}</p>
                    </div>
                    <p class="mt-2 flex flex-wrap items-center gap-2 text-2xs text-cream-3">
                        <span class="badge badge-idle">{{ $p->methodLabel() }}</span>
                        @if($p->order)<a href="{{ route('admin.orders.show', $p->order) }}" class="num inline-block py-1 hover:text-flame-ink-hi">{{ $p->order->code }}</a>@else<span>على الحساب</span>@endif
                        @if($p->reference)<span class="num">{{ $p->reference }}</span>@endif
                    </p>
                </li>
            @endforeach
        </ul>

        <div class="panel mt-4 hidden overflow-x-auto lg:block">
            <table class="tbl">
                <thead>
                    <tr><th>الكود</th><th>التاريخ</th><th>العميل</th><th>الطريقة</th><th>على أوردر</th><th>مرجع</th><th class="text-end">المبلغ</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr>
                            <td><span class="num text-2xs text-cream-3">{{ $p->code }}</span></td>
                            <td class="num whitespace-nowrap">{{ $p->paid_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.customers.show', $p->customer_id) }}" class="strong hover:text-flame-ink-hi">{{ $p->customer?->name }}</a></td>
                            <td>{{ $p->methodLabel() }}</td>
                            <td>
                                @if($p->order)
                                    <a href="{{ route('admin.orders.show', $p->order) }}" class="num text-2xs hover:text-flame-ink-hi">{{ $p->order->code }}</a>
                                @else
                                    <span class="text-2xs text-cream-3">على الحساب</span>
                                @endif
                            </td>
                            <td><span class="num text-2xs">{{ $p->reference ?: '—' }}</span></td>
                            <td class="num text-end font-semibold text-good">{{ \App\Support\Money::format($p->amount) }}</td>
                            <td class="text-end">
                                @if(auth()->user()->can_('payments.edit'))
                                    <form method="POST" action="{{ route('admin.payments.destroy', $p) }}"
                                          data-confirm="هتتشال دفعة {{ \App\Support\Money::format($p->amount) }} {{ $currency }} من حساب {{ $p->customer?->name }}. الرصيد هيزيد بنفس المبلغ.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon text-cream-3" aria-label="امسح الدفعة {{ $p->code }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $payments->links() }}</div>
    @endif
@endsection

@push('sheets')
    @if(auth()->user()->can_('payments.edit'))
        <dialog class="sheet" data-sheet="new-payment" aria-labelledby="np-h">
            <div class="sheet-panel">
                <div class="sheet-grip" aria-hidden="true"></div>
                <h2 id="np-h" class="text-base font-semibold text-cream">دفعة جديدة</h2>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="mt-5 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label for="np-customer" class="label">العميل <span class="text-flame-ink">*</span></label>
                        <input type="search" data-select-filter="#np-customer" placeholder="فلتر بالاسم…" class="field mb-2 text-xs" aria-label="فلترة العملاء">
                        <select id="np-customer" name="customer_id" required class="field">
                            <option value="">اختار…</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(request('customer') == $c->id)>{{ $c->name }} — {{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="np-order" class="label">على أوردر <span class="text-cream-3">(اختياري)</span></label>
                        <select id="np-order" name="order_id" class="field">
                            <option value="">دفعة على الحساب</option>
                            @foreach($openOrders as $o)
                                <option value="{{ $o->id }}">{{ $o->code }} — {{ $o->customer?->name }} — باقي {{ \App\Support\Money::short($o->dueAmount()) }}</option>
                            @endforeach
                        </select>
                        <p class="hint">لو سبته فاضي، الدفعة هتتخصم من إجمالي حساب العميل.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="np-amount" class="label">المبلغ <span class="text-flame-ink">*</span></label>
                            <input id="np-amount" name="amount" type="number" step="0.01" min="0.01" required dir="ltr" class="field num text-start">
                        </div>
                        <div>
                            <label for="np-date" class="label">التاريخ</label>
                            <input id="np-date" name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="field num">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="np-method" class="label">الطريقة</label>
                            <select id="np-method" name="method" class="field">
                                @foreach(\App\Models\Payment::METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="np-ref" class="label">مرجع</label>
                            <input id="np-ref" name="reference" class="field" dir="ltr">
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-1">
                        <button type="submit" class="btn btn-primary flex-1">سجّل الدفعة</button>
                        <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endpush
