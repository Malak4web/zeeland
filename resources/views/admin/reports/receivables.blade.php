@extends('layouts.admin')

@section('title', 'المديونيات')
@section('subtitle', 'مين عليه إيه، وبقاله قد إيه')
@section('back', route('admin.reports.index'))

@section('actions')
    <button type="button" onclick="window.print()" class="btn btn-ghost btn-sm no-print">طباعة</button>
@endsection

@section('content')
    <div class="panel p-5">
        <p class="text-2xs text-cream-3">إجمالي المستحق</p>
        <p class="mt-2 flex items-baseline gap-2 leading-none">
            <span class="num text-[clamp(1.9rem,1.3rem+2.5vw,2.8rem)] font-semibold text-flame-ink print:text-black">{{ \App\Support\Money::format($total) }}</span>
            <span class="text-sm text-cream-3">{{ $currency }}</span>
        </p>

        {{-- Ageing: the same total, split by how long it has been sitting. The
             bar reads left-to-right by risk, and each band carries its number. --}}
        @php $sum = max(0.01, array_sum($buckets)); @endphp
        <div class="mt-6 flex h-3 overflow-hidden rounded-full" role="img"
             aria-label="توزيع المديونية حسب العمر">
            @foreach([
                ['current', 'bg-good'],
                ['d30', 'bg-frost'],
                ['d60', 'bg-warn'],
                ['d90', 'bg-bad'],
            ] as [$key, $class])
                @if($buckets[$key] > 0)
                    <div class="{{ $class }}" style="width:{{ $buckets[$key] / $sum * 100 }}%"></div>
                @endif
            @endforeach
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach([
                ['أقل من 30 يوم', 'current', 'text-good'],
                ['30–60 يوم', 'd30', 'text-frost-ink'],
                ['60–90 يوم', 'd60', 'text-warn'],
                ['أكتر من 90 يوم', 'd90', 'text-bad'],
            ] as [$label, $key, $tone])
                <div>
                    <dt class="text-2xs text-cream-3">{{ $label }}</dt>
                    <dd class="num mt-1 text-lg font-semibold {{ $tone }} print:text-black">{{ \App\Support\Money::short($buckets[$key]) }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    @if($customers->isEmpty())
        <x-empty class="mt-6" title="مفيش مديونيات" hint="كل العملاء مسدّدين بالكامل." />
    @else
        <section class="panel mt-4" aria-labelledby="cust-h">
            <div class="panel-head">
                <h2 id="cust-h" class="panel-title">حسب العميل</h2>
                <span class="num text-2xs text-cream-3">{{ $customers->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr><th>العميل</th><th>الموبايل</th><th class="text-end">مبيعات</th><th class="text-end">تحصيل</th><th class="text-end">الباقي</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $c) }}" class="strong hover:text-flame-ink-hi">{{ $c->name }}</a>
                                    <span class="num block text-2xs text-cream-3">{{ $c->code }}</span>
                                </td>
                                <td><span class="num">{{ $c->phone }}</span></td>
                                <td class="num text-end">{{ \App\Support\Money::short($c->totalBilled()) }}</td>
                                <td class="num text-end text-good print:text-black">{{ \App\Support\Money::short($c->totalPaid()) }}</td>
                                <td class="num text-end font-semibold {{ $c->overCreditLimit() ? 'text-bad' : 'text-flame-ink' }} print:text-black">{{ \App\Support\Money::format($c->computed_balance) }}</td>
                                <td class="text-end no-print">
                                    <a href="{{ route('admin.customers.statement', $c) }}" class="btn btn-ghost btn-sm">كشف</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel mt-4" aria-labelledby="ord-h">
            <div class="panel-head">
                <h2 id="ord-h" class="panel-title">الأوردرات اللي لسه عليها فلوس</h2>
                <span class="num text-2xs text-cream-3">{{ $unpaid->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr><th>الأوردر</th><th>العميل</th><th>التاريخ</th><th class="text-end">العمر</th><th class="text-end">الإجمالي</th><th class="text-end">الباقي</th></tr>
                    </thead>
                    <tbody>
                        @foreach($unpaid as $order)
                            @php
                                $age = (int) $order->order_date->diffInDays(now());
                                $terms = (int) ($order->customer?->payment_terms_days ?? 0);
                                $late = $terms > 0 ? $age > $terms : $age > 30;
                            @endphp
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="num text-2xs hover:text-flame-ink-hi">{{ $order->code }}</a></td>
                                <td><a href="{{ route('admin.customers.show', $order->customer_id) }}" class="strong hover:text-flame-ink-hi">{{ $order->customer?->name }}</a></td>
                                <td class="num whitespace-nowrap">{{ $order->order_date->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    {{-- Late is a word plus a colour, never a colour alone. --}}
                                    <span class="badge {{ $late ? 'badge-bad' : 'badge-idle' }}">
                                        <span class="num">{{ $age }}</span> يوم{{ $late ? ' · متأخّر' : '' }}
                                    </span>
                                </td>
                                <td class="num text-end">{{ \App\Support\Money::format($order->total) }}</td>
                                <td class="num text-end font-semibold text-flame-ink print:text-black">{{ \App\Support\Money::format($order->dueAmount()) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
