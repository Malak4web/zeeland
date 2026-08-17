@extends('layouts.admin')

@section('title', 'الأوردرات')
@section('subtitle', $orders->total().' أوردر')

@section('actions')
    <button type="button" data-sheet-open="order-filters" class="btn btn-ghost btn-sm lg:hidden">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
        فلترة
    </button>
    @if(auth()->user()->can_('orders.edit'))
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">أوردر جديد</span>
        </a>
    @endif
@endsection

@section('content')
    {{-- The filtered range totals sit above the list, so a date filter answers
         "بعنا كام في الفترة دي؟" without opening the reports screen. --}}
    <div class="panel flex flex-wrap items-center gap-x-8 gap-y-3 p-4">
        <div>
            <p class="text-2xs text-cream-3">إجمالي الفترة المعروضة</p>
            <p class="num mt-1 text-xl font-semibold text-cream">{{ \App\Support\Money::format($rangeTotal) }} <span class="text-2xs font-normal text-cream-3">{{ $currency }}</span></p>
        </div>
        <div>
            <p class="text-2xs text-cream-3">عدد الأوردرات</p>
            <p class="num mt-1 text-xl font-semibold text-cream">{{ $rangeCount }}</p>
        </div>
        <div>
            <p class="text-2xs text-cream-3">متوسط الأوردر</p>
            <p class="num mt-1 text-xl font-semibold text-cream">{{ $rangeCount > 0 ? \App\Support\Money::short($rangeTotal / $rangeCount) : '0' }}</p>
        </div>
    </div>

    <form method="GET" data-auto-filter class="mt-4 hidden flex-wrap items-end gap-2 lg:flex">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="كود أوردر أو اسم عميل…" class="field max-w-xs" aria-label="بحث في الأوردرات">
        <select name="customer" class="field max-w-[13rem]" aria-label="فلترة بالعميل">
            <option value="">كل العملاء</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(request('customer') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="field max-w-[9rem]" aria-label="فلترة بالحالة">
            <option value="">كل الحالات</option>
            @foreach(\App\Models\Order::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="field num max-w-[10rem]" aria-label="من تاريخ">
        <input type="date" name="to" value="{{ request('to') }}" class="field num max-w-[10rem]" aria-label="إلى تاريخ">
        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-navy-2 px-3 py-2.5 text-xs text-cream-2">
            <input type="checkbox" name="due" value="1" @checked(request('due') === '1') class="size-4 accent-flame">
            عليها فلوس بس
        </label>
        @if(request()->hasAny(['q','customer','status','from','to','due']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">مسح</a>
        @endif
    </form>

    @if($orders->isEmpty())
        <x-empty class="mt-6" title="مفيش أوردرات هنا"
                 hint="كل أوردر بتسجّله بيتحسب في كشف حساب العميل وفي تقارير المبيعات تلقائيًا."
                 :action="auth()->user()->can_('orders.edit') ? 'سجّل أوردر' : null"
                 :href="auth()->user()->can_('orders.edit') ? route('admin.orders.create') : null" />
    @else
        <ul class="mt-4 flex flex-col gap-2.5 lg:hidden">
            @foreach($orders as $order)
                <li class="panel">
                    <a href="{{ route('admin.orders.show', $order) }}" class="block p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-cream">{{ $order->customer?->name }}</p>
                                <p class="num mt-0.5 text-2xs text-cream-3">{{ $order->code }} · {{ $order->order_date->format('Y-m-d') }}</p>
                            </div>
                            <p class="num shrink-0 text-base font-semibold text-cream">{{ \App\Support\Money::short($order->total) }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="badge {{ match($order->status) { 'delivered' => 'badge-good', 'confirmed' => 'badge-frost', 'cancelled' => 'badge-idle', default => 'badge-warn' } }}">{{ $order->statusLabel() }}</span>
                            <span class="badge {{ match($order->paymentStatus()) { 'paid' => 'badge-good', 'partial' => 'badge-warn', 'unpaid' => 'badge-bad', default => 'badge-idle' } }}">{{ $order->paymentStatusLabel() }}</span>
                            @if($order->dueAmount() > 0.005)
                                <span class="num text-2xs text-flame-ink">باقي {{ \App\Support\Money::short($order->dueAmount()) }}</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="panel mt-4 hidden overflow-x-auto lg:block">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>الكود</th><th>التاريخ</th><th>العميل</th><th>الحالة</th>
                        <th class="text-end">الإجمالي</th><th class="text-end">مدفوع</th><th class="text-end">الباقي</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="num text-2xs text-cream-3 hover:text-flame-ink-hi">{{ $order->code }}</a></td>
                            <td class="num whitespace-nowrap">{{ $order->order_date->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.customers.show', $order->customer_id) }}" class="strong hover:text-flame-ink-hi">{{ $order->customer?->name }}</a></td>
                            <td>
                                <span class="badge {{ match($order->status) { 'delivered' => 'badge-good', 'confirmed' => 'badge-frost', 'cancelled' => 'badge-idle', default => 'badge-warn' } }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td class="num text-end strong">{{ \App\Support\Money::format($order->total) }}</td>
                            <td class="num text-end text-good">{{ \App\Support\Money::format($order->paidAmount()) }}</td>
                            <td class="num text-end {{ $order->dueAmount() > 0.005 ? 'text-flame-ink' : 'text-cream-3' }}">{{ \App\Support\Money::format(max(0, $order->dueAmount())) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.print', $order) }}" class="btn btn-ghost btn-sm">إذن</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    @endif
@endsection

@push('sheets')
    <dialog class="sheet lg:hidden" data-sheet="order-filters" aria-labelledby="of-h">
        <div class="sheet-panel">
            <div class="sheet-grip" aria-hidden="true"></div>
            <h2 id="of-h" class="pb-4 text-base font-semibold text-cream">فلترة الأوردرات</h2>
            <form method="GET" class="flex flex-col gap-4">
                <div>
                    <label for="of-q" class="label">بحث</label>
                    <input id="of-q" type="search" name="q" value="{{ request('q') }}" class="field" placeholder="كود أو اسم عميل…">
                </div>
                <div>
                    <label for="of-customer" class="label">العميل</label>
                    <select id="of-customer" name="customer" class="field">
                        <option value="">الكل</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(request('customer') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="of-from" class="label">من</label>
                        <input id="of-from" type="date" name="from" value="{{ request('from') }}" class="field num">
                    </div>
                    <div>
                        <label for="of-to" class="label">إلى</label>
                        <input id="of-to" type="date" name="to" value="{{ request('to') }}" class="field num">
                    </div>
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-navy-2 p-3 text-sm text-cream-2">
                    <input type="checkbox" name="due" value="1" @checked(request('due') === '1') class="size-4 accent-flame">
                    اللي عليها فلوس بس
                </label>
                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="btn btn-primary flex-1">طبّق</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost flex-1">مسح</a>
                </div>
            </form>
        </div>
    </dialog>
@endpush
