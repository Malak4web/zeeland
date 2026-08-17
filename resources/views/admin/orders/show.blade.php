@extends('layouts.admin')

@section('title', $order->code)
@section('subtitle', $order->customer?->name.' · '.$order->order_date->translatedFormat('j F Y'))
@section('back', route('admin.orders.index'))

@section('actions')
    <a href="{{ route('admin.orders.print', $order) }}" class="btn btn-ghost btn-sm">إذن تسليم</a>
    @if(auth()->user()->can_('orders.edit'))
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-solid btn-sm">تعديل</a>
    @endif
@endsection

@section('content')
    <div class="grid gap-4 lg:grid-cols-12">

        <section class="panel lg:col-span-8" aria-labelledby="i-h">
            <div class="panel-head">
                <h2 id="i-h" class="panel-title">الأصناف</h2>
                {{-- Both states in words, not just the progress bar's colour. --}}
                <span class="flex flex-wrap gap-2">
                    <span class="badge {{ match($order->status) { 'delivered' => 'badge-good', 'confirmed' => 'badge-frost', 'cancelled' => 'badge-idle', default => 'badge-warn' } }}">{{ $order->statusLabel() }}</span>
                    @if($order->isBillable())
                        <span class="badge {{ match($order->paymentStatus()) { 'paid' => 'badge-good', 'partial' => 'badge-warn', 'unpaid' => 'badge-bad', default => 'badge-idle' } }}">{{ $order->paymentStatusLabel() }}</span>
                    @endif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>الصنف</th><th class="text-end">الكمية</th><th class="text-end">السعر</th><th class="text-end">الإجمالي</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td class="strong">{{ $item->name }}</td>
                                <td class="num text-end">{{ \App\Support\Money::short($item->quantity) }} <span class="text-2xs text-cream-3">{{ $item->unit }}</span></td>
                                <td class="num text-end">{{ \App\Support\Money::format($item->unit_price) }}</td>
                                <td class="num text-end strong">{{ \App\Support\Money::format($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <dl class="flex flex-col gap-2 border-t border-navy-2 p-4">
                <div class="flex justify-between text-sm"><dt class="text-cream-3">مجموع الأصناف</dt><dd class="num text-cream-2">{{ \App\Support\Money::format($order->subtotal) }}</dd></div>
                @if($order->discount > 0)
                    <div class="flex justify-between text-sm"><dt class="text-cream-3">خصم</dt><dd class="num text-cream-2">− {{ \App\Support\Money::format($order->discount) }}</dd></div>
                @endif
                @if($order->shipping > 0)
                    <div class="flex justify-between text-sm"><dt class="text-cream-3">شحن</dt><dd class="num text-cream-2">+ {{ \App\Support\Money::format($order->shipping) }}</dd></div>
                @endif
                <div class="mt-1 flex items-baseline justify-between border-t border-navy-2 pt-3">
                    <dt class="text-sm text-cream">الإجمالي</dt>
                    <dd class="num text-xl font-semibold text-cream">{{ \App\Support\Money::format($order->total) }} <span class="text-2xs font-normal text-cream-3">{{ $currency }}</span></dd>
                </div>
            </dl>

            @if($order->notes)
                <p class="border-t border-navy-2 p-4 text-sm leading-[1.9] text-cream-2">{{ $order->notes }}</p>
            @endif
        </section>

        <div class="flex flex-col gap-4 lg:col-span-4">
            <section class="panel p-5" aria-labelledby="pay-h">
                <h2 id="pay-h" class="text-sm font-semibold text-cream">السداد</h2>

                @php $due = max(0, $order->dueAmount()); @endphp

                <p class="mt-4 flex items-baseline gap-2 leading-none">
                    <span class="num text-2xl font-semibold {{ $due > 0.005 ? 'text-flame-ink' : 'text-good' }}">{{ \App\Support\Money::format($due) }}</span>
                    <span class="text-2xs text-cream-3">{{ $currency }} باقي</span>
                </p>

                {{-- A bar is faster to read than two numbers, and the numbers are
                     right underneath it anyway. --}}
                @php $pct = (float) $order->total > 0 ? min(100, round($order->paidAmount() / (float) $order->total * 100)) : 0; @endphp
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-navy-2" role="img" aria-label="اتدفع {{ $pct }} بالمية">
                    <div class="h-full rounded-full bg-good" style="width:{{ $pct }}%"></div>
                </div>
                <p class="mt-2 flex justify-between text-2xs text-cream-3">
                    <span>اتدفع <span class="num text-good">{{ \App\Support\Money::format($order->paidAmount()) }}</span></span>
                    <span class="num">{{ $pct }}%</span>
                </p>

                @if(auth()->user()->can_('payments.edit') && $due > 0.005 && $order->isBillable())
                    <button type="button" data-sheet-open="pay" class="btn btn-primary mt-5 w-full">سجّل دفعة</button>
                @endif

                @if($order->payments->isNotEmpty())
                    <ul class="mt-5 flex flex-col gap-2 border-t border-navy-2 pt-4">
                        @foreach($order->payments as $p)
                            <li class="flex items-center justify-between gap-3 text-2xs">
                                <span class="text-cream-3"><span class="num">{{ $p->paid_at->format('Y-m-d') }}</span> · {{ $p->methodLabel() }}</span>
                                <span class="num text-good">{{ \App\Support\Money::short($p->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="panel p-5" aria-labelledby="meta-h">
                <h2 id="meta-h" class="text-sm font-semibold text-cream">تفاصيل</h2>
                <dl class="mt-4 flex flex-col gap-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-2xs text-cream-3">العميل</dt>
                        <dd><a href="{{ route('admin.customers.show', $order->customer_id) }}" class="inline-block py-1 text-cream hover:text-flame-ink-hi">{{ $order->customer?->name }}</a></dd>
                    </div>
                    <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">تاريخ الأوردر</dt><dd class="num text-cream-2">{{ $order->order_date->format('Y-m-d') }}</dd></div>
                    @if($order->delivery_date)
                        <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">التسليم</dt><dd class="num text-cream-2">{{ $order->delivery_date->format('Y-m-d') }}</dd></div>
                    @endif
                    @if($order->delivery_address)
                        <div class="flex justify-between gap-3"><dt class="shrink-0 text-2xs text-cream-3">العنوان</dt><dd class="text-end text-cream-2">{{ $order->delivery_address }}</dd></div>
                    @endif
                    @if($order->creator)
                        <div class="flex justify-between gap-3"><dt class="text-2xs text-cream-3">سجّله</dt><dd class="text-cream-2">{{ $order->creator->name }}</dd></div>
                    @endif
                </dl>
            </section>

            @if(auth()->user()->can_('orders.edit'))
                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                      data-confirm="هيتمسح الأوردر {{ $order->code }} وكل سطوره. ده بينفع بس لو مافيش دفعات عليه.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-full">امسح الأوردر</button>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('sheets')
    @if(auth()->user()->can_('payments.edit'))
        <dialog class="sheet" data-sheet="pay" aria-labelledby="ps-h">
            <div class="sheet-panel">
                <div class="sheet-grip" aria-hidden="true"></div>
                <h2 id="ps-h" class="text-base font-semibold text-cream">دفعة على {{ $order->code }}</h2>
                <p class="mt-1 text-2xs text-cream-3">{{ $order->customer?->name }} · باقي <span class="num">{{ \App\Support\Money::format(max(0, $order->dueAmount())) }}</span> {{ $currency }}</p>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="mt-5 flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $order->customer_id }}">
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    <div>
                        <label for="os-amount" class="label">المبلغ ({{ $currency }})</label>
                        <input id="os-amount" name="amount" type="number" step="0.01" min="0.01" required dir="ltr"
                               value="{{ max(0, round($order->dueAmount(), 2)) }}" class="field num text-start" autofocus>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="os-method" class="label">الطريقة</label>
                            <select id="os-method" name="method" class="field">
                                @foreach(\App\Models\Payment::METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="os-date" class="label">التاريخ</label>
                            <input id="os-date" name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="field num">
                        </div>
                    </div>

                    <div>
                        <label for="os-ref" class="label">مرجع</label>
                        <input id="os-ref" name="reference" class="field" dir="ltr">
                    </div>

                    <div class="flex gap-2.5 pt-1">
                        <button type="submit" class="btn btn-primary flex-1">سجّل</button>
                        <button type="button" data-sheet-close class="btn btn-ghost">إلغاء</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endpush
