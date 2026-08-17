@extends('layouts.admin')

@section('title', $customer->name)
@section('subtitle', $customer->code.' · '.$customer->typeLabel())
@section('back', route('admin.customers.index'))

@section('actions')
    <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-ghost btn-sm">كشف حساب</a>
    @if(auth()->user()->can_('orders.edit'))
        <a href="{{ route('admin.orders.create', ['customer' => $customer->id]) }}" class="btn btn-primary btn-sm">
            <span class="max-sm:sr-only">أوردر</span>
            <svg class="sm:hidden" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        </a>
    @endif
@endsection

@section('content')
    @php
        $balance = $totals['closing'];
        $max = max(1, collect($monthly)->max('value'));
    @endphp

    <div class="grid gap-4 lg:grid-cols-12">

        {{-- The account, first and biggest: it is why this page gets opened. --}}
        <section class="panel relative overflow-clip p-5 lg:col-span-5">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(120%_90%_at_100%_0%,var(--flame),transparent_60%)] {{ $balance > 0.005 ? 'opacity-[0.13]' : 'opacity-0' }}" aria-hidden="true"></div>

            <p class="text-2xs text-cream-3">{{ $balance > 0.005 ? 'عليه لينا' : ($balance < -0.005 ? 'ليه عندنا' : 'الحساب مسدّد') }}</p>
            <p class="mt-2 flex items-baseline gap-2 leading-none">
                <span class="num text-[clamp(1.9rem,1.3rem+2.5vw,2.8rem)] font-semibold {{ $balance > 0.005 ? ($customer->overCreditLimit() ? 'text-bad' : 'text-flame-ink') : 'text-good' }}">{{ \App\Support\Money::format(abs($balance)) }}</span>
                <span class="text-sm text-cream-3">{{ $currency }}</span>
            </p>

            @if($customer->overCreditLimit())
                <p class="badge badge-bad mt-3">عدّى حد الائتمان ({{ \App\Support\Money::short($customer->credit_limit) }})</p>
            @endif

            <dl class="mt-5 grid grid-cols-3 gap-3 border-t border-navy-2 pt-4">
                <div>
                    <dt class="text-2xs text-cream-3">رصيد افتتاحي</dt>
                    <dd class="num mt-1 text-sm text-cream-2">{{ \App\Support\Money::short($totals['opening']) }}</dd>
                </div>
                <div>
                    <dt class="text-2xs text-cream-3">إجمالي مبيعات</dt>
                    <dd class="num mt-1 text-sm text-cream">{{ \App\Support\Money::short($totals['debit']) }}</dd>
                </div>
                <div>
                    <dt class="text-2xs text-cream-3">إجمالي تحصيل</dt>
                    <dd class="num mt-1 text-sm text-good">{{ \App\Support\Money::short($totals['credit']) }}</dd>
                </div>
            </dl>

            @if(auth()->user()->can_('payments.edit') && $balance > 0.005)
                <button type="button" data-sheet-open="pay" class="btn btn-primary mt-5 w-full">سجّل دفعة</button>
            @endif
        </section>

        {{-- Contact + terms, the things you reach for mid-phone-call. --}}
        <section class="panel p-5 lg:col-span-4" aria-labelledby="c-h">
            <div class="flex items-start justify-between gap-3">
                <h2 id="c-h" class="text-sm font-semibold text-cream">التواصل والشروط</h2>
                @if(auth()->user()->can_('customers.edit'))
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">تعديل</a>
                @endif
            </div>

            <dl class="mt-4 flex flex-col gap-3 text-sm">
                @if($customer->contact_name)
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">المسؤول</dt><dd class="text-cream-2">{{ $customer->contact_name }}</dd></div>
                @endif
                <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">موبايل</dt><dd><a href="tel:{{ $customer->phone }}" class="num inline-block py-1 text-cream-2 hover:text-flame-ink-hi">{{ $customer->phone }}</a></dd></div>
                @if($customer->governorate)
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">المحافظة</dt><dd class="text-cream-2">{{ $customer->governorate }}</dd></div>
                @endif
                @if($customer->price_per_pack)
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">سعر الشيكارة</dt><dd class="num text-cream">{{ \App\Support\Money::format($customer->price_per_pack) }}</dd></div>
                @endif
                @if($customer->payment_terms_days)
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">مهلة السداد</dt><dd class="text-cream-2"><span class="num">{{ $customer->payment_terms_days }}</span> يوم</dd></div>
                @endif
                @if($customer->credit_limit > 0)
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">حد الائتمان</dt><dd class="num text-cream-2">{{ \App\Support\Money::short($customer->credit_limit) }}</dd></div>
                @endif
            </dl>

            <div class="mt-5 flex gap-2">
                <a href="tel:{{ $customer->phone }}" class="btn btn-ghost btn-sm flex-1">اتصال</a>
                <a href="{{ $customer->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm flex-1">واتساب</a>
            </div>

            @if($customer->notes)
                <p class="mt-4 rounded-xl border border-navy-2 bg-navy/40 p-3 text-2xs leading-[1.8] text-cream-2">{{ $customer->notes }}</p>
            @endif
        </section>

        {{-- Six months of value, drawn as bars — spotting a customer who went
             quiet is the whole point of putting it here. --}}
        <section class="panel p-5 lg:col-span-3" aria-labelledby="m-h">
            <h2 id="m-h" class="text-sm font-semibold text-cream">آخر 6 شهور</h2>
            <ul class="mt-4 flex h-20 items-stretch gap-1.5">
                @foreach($monthly as $m)
                    <li class="flex flex-1 flex-col items-center justify-end">
                        <span class="block w-full rounded-t-[3px] {{ $m['value'] > 0 ? 'bg-flame/70' : 'bg-navy-2' }}"
                              style="height:{{ max(3, round($m['value'] / $max * 100)) }}%"
                              title="{{ $m['label'] }} — {{ \App\Support\Money::format($m['value']) }}"></span>
                    </li>
                @endforeach
            </ul>
            <ul class="mt-2 flex gap-1.5 text-2xs text-cream-3" aria-hidden="true">
                @foreach($monthly as $m)<li class="flex-1 truncate text-center">{{ $m['label'] }}</li>@endforeach
            </ul>
            <table class="sr-only">
                <caption>مبيعات آخر 6 شهور</caption>
                <tbody>@foreach($monthly as $m)<tr><td>{{ $m['label'] }}</td><td>{{ \App\Support\Money::format($m['value']) }}</td></tr>@endforeach</tbody>
            </table>
        </section>

        {{-- Orders --}}
        <section class="panel lg:col-span-7" aria-labelledby="o-h">
            <div class="panel-head">
                <h2 id="o-h" class="panel-title">الأوردرات</h2>
                <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">كلهم ←</a>
            </div>

            @if($orders->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش أوردرات لسه"
                         :action="auth()->user()->can_('orders.edit') ? 'سجّل أوردر' : null"
                         :href="auth()->user()->can_('orders.edit') ? route('admin.orders.create', ['customer' => $customer->id]) : null" />
            @else
                <ul class="divide-y divide-navy-2">
                    @foreach($orders as $order)
                        <li>
                            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-navy/50">
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="num text-2xs text-cream-3">{{ $order->code }}</span>
                                        <span class="num text-2xs text-cream-3">{{ $order->order_date->format('Y-m-d') }}</span>
                                        <span class="badge {{ match($order->paymentStatus()) { 'paid' => 'badge-good', 'partial' => 'badge-warn', 'unpaid' => 'badge-bad', default => 'badge-idle' } }}">{{ $order->paymentStatusLabel() }}</span>
                                    </span>
                                    <span class="mt-1 block truncate text-sm text-cream-2">
                                        @foreach($order->items as $item)
                                            <span class="num">{{ \App\Support\Money::short($item->quantity) }}</span> {{ $item->unit }} {{ $item->name }}@if(!$loop->last) · @endif
                                        @endforeach
                                    </span>
                                </span>
                                <span class="shrink-0 text-end">
                                    <span class="num block text-sm font-semibold text-cream">{{ \App\Support\Money::short($order->total) }}</span>
                                    @if($order->dueAmount() > 0.005)
                                        <span class="num block text-2xs text-flame-ink">باقي {{ \App\Support\Money::short($order->dueAmount()) }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Payments --}}
        <section class="panel lg:col-span-5" aria-labelledby="p-h">
            <div class="panel-head">
                <h2 id="p-h" class="panel-title">الدفعات</h2>
                @if(auth()->user()->can_('payments.edit'))
                    <button type="button" data-sheet-open="pay" class="px-1 py-1 text-2xs text-flame-ink-hi">+ دفعة</button>
                @endif
            </div>

            @if($payments->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش دفعات متسجّلة" />
            @else
                <ul class="divide-y divide-navy-2">
                    @foreach($payments as $p)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <span class="min-w-0">
                                <span class="num block text-2xs text-cream-3">{{ $p->code }} · {{ $p->paid_at->format('Y-m-d') }}</span>
                                <span class="block text-sm text-cream-2">{{ $p->methodLabel() }}@if($p->reference) · <span class="num">{{ $p->reference }}</span>@endif</span>
                            </span>
                            <span class="num shrink-0 text-sm font-semibold text-good">{{ \App\Support\Money::short($p->amount) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection

@push('sheets')
    @if(auth()->user()->can_('payments.edit'))
        <dialog class="sheet" data-sheet="pay" aria-labelledby="pay-h">
            <div class="sheet-panel">
                <div class="sheet-grip" aria-hidden="true"></div>
                <h2 id="pay-h" class="text-base font-semibold text-cream">دفعة من {{ $customer->name }}</h2>
                <p class="mt-1 text-2xs text-cream-3">الرصيد الحالي <span class="num">{{ \App\Support\Money::format($balance) }}</span> {{ $currency }}</p>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="mt-5 flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <div>
                        <label for="pay-amount" class="label">المبلغ ({{ $currency }}) <span class="text-flame-ink">*</span></label>
                        <input id="pay-amount" name="amount" type="number" step="0.01" min="0.01" required dir="ltr"
                               value="{{ max(0, round($balance, 2)) }}" class="field num text-start" autofocus>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="pay-method" class="label">الطريقة</label>
                            <select id="pay-method" name="method" class="field">
                                @foreach(\App\Models\Payment::METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pay-date" class="label">التاريخ</label>
                            <input id="pay-date" name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="field num">
                        </div>
                    </div>

                    <div>
                        <label for="pay-order" class="label">على أوردر معيّن <span class="text-cream-3">(اختياري)</span></label>
                        <select id="pay-order" name="order_id" class="field">
                            <option value="">دفعة على الحساب</option>
                            @foreach($orders as $order)
                                @if($order->dueAmount() > 0.005)
                                    <option value="{{ $order->id }}">{{ $order->code }} — باقي {{ \App\Support\Money::short($order->dueAmount()) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="pay-ref" class="label">مرجع <span class="text-cream-3">(رقم تحويل / شيك)</span></label>
                        <input id="pay-ref" name="reference" class="field" dir="ltr">
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
