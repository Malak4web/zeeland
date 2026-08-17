@extends('layouts.admin')

@section('title', 'التقارير')
@section('subtitle', $from->format('Y-m-d').' → '.$to->format('Y-m-d'))

@section('actions')
    <a href="{{ route('admin.reports.receivables') }}" class="btn btn-ghost btn-sm">المديونيات</a>
    <button type="button" onclick="window.print()" class="btn btn-solid btn-sm no-print max-sm:hidden">طباعة</button>
@endsection

@section('content')
    <form method="GET" data-auto-filter class="no-print flex flex-wrap items-end gap-3">
        <div>
            <label for="from" class="label">من</label>
            <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" class="field num">
        </div>
        <div>
            <label for="to" class="label">إلى</label>
            <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" class="field num">
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach([
                'الشهر ده' => [now()->startOfMonth(), now()],
                'الشهر اللي فات' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
                'السنة دي' => [now()->startOfYear(), now()],
            ] as $label => [$f, $t])
                <a href="{{ route('admin.reports.index', ['from' => $f->toDateString(), 'to' => $t->toDateString()]) }}"
                   class="btn btn-ghost btn-sm">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    <div class="mt-5 grid gap-4 lg:grid-cols-12">

        <div class="panel grid grid-cols-2 divide-navy-2 lg:col-span-12 lg:grid-cols-4 lg:divide-x lg:divide-x-reverse">
            <div class="border-b border-navy-2 p-4 lg:border-b-0">
                <p class="text-2xs text-cream-3">مبيعات الفترة</p>
                <p class="num mt-1.5 text-xl font-semibold text-cream">{{ \App\Support\Money::format($sold) }}</p>
                <p class="mt-0.5 text-2xs text-cream-3">{{ $currency }}</p>
            </div>
            <div class="border-b border-navy-2 p-4 lg:border-b-0">
                <p class="text-2xs text-cream-3">تحصيل الفترة</p>
                <p class="num mt-1.5 text-xl font-semibold text-good">{{ \App\Support\Money::format($collected) }}</p>
                <p class="mt-0.5 text-2xs text-cream-3">{{ $sold > 0 ? round($collected / $sold * 100).'% من المبيعات' : '—' }}</p>
            </div>
            <div class="p-4">
                <p class="text-2xs text-cream-3">عدد الأوردرات</p>
                <p class="num mt-1.5 text-xl font-semibold text-cream">{{ $orders }}</p>
                <p class="mt-0.5 text-2xs text-cream-3">متوسط <span class="num">{{ $orders > 0 ? \App\Support\Money::short($sold / $orders) : '0' }}</span></p>
            </div>
            <div class="p-4">
                <p class="text-2xs text-cream-3">تحويل طلبات الموقع</p>
                <p class="num mt-1.5 text-xl font-semibold {{ $leadStats['rate'] >= 25 ? 'text-good' : 'text-cream' }}">{{ $leadStats['rate'] }}%</p>
                <p class="mt-0.5 text-2xs text-cream-3"><span class="num">{{ $leadStats['won'] }}</span> من <span class="num">{{ $leadStats['total'] }}</span></p>
            </div>
        </div>

        @if($byMonth->isNotEmpty())
            @php $mMax = max(1, $byMonth->max('total')); @endphp
            <section class="panel p-5 lg:col-span-7" aria-labelledby="bm-h">
                <h2 id="bm-h" class="text-sm font-semibold text-cream">المبيعات بالشهر</h2>
                <ul class="mt-5 flex h-36 items-stretch gap-2">
                    @foreach($byMonth as $m)
                        <li class="group flex flex-1 flex-col items-center justify-end gap-2">
                            <span class="num text-2xs text-cream-3">{{ \App\Support\Money::short($m->total) }}</span>
                            <span class="block w-full rounded-t-[3px] bg-flame/75 transition-colors group-hover:bg-flame"
                                  style="height:{{ max(3, round($m->total / $mMax * 100)) }}%"></span>
                        </li>
                    @endforeach
                </ul>
                <ul class="mt-2 flex gap-2 text-2xs text-cream-3" aria-hidden="true">
                    @foreach($byMonth as $m)<li class="num flex-1 text-center">{{ $m->ym }}</li>@endforeach
                </ul>
                <table class="sr-only">
                    <caption>المبيعات بالشهر</caption>
                    <tbody>@foreach($byMonth as $m)<tr><td>{{ $m->ym }}</td><td>{{ \App\Support\Money::format($m->total) }}</td><td>{{ $m->n }} أوردر</td></tr>@endforeach</tbody>
                </table>
            </section>
        @endif

        <section class="panel lg:col-span-5" aria-labelledby="bp-h">
            <div class="panel-head"><h2 id="bp-h" class="panel-title">المبيعات بالصنف</h2></div>
            @if($byProduct->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش مبيعات في الفترة دي" />
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>الصنف</th><th class="text-end">الكمية</th><th class="text-end">القيمة</th></tr></thead>
                        <tbody>
                            @foreach($byProduct as $p)
                                <tr>
                                    <td class="strong">{{ $p->name }}</td>
                                    <td class="num text-end">{{ \App\Support\Money::short($p->qty) }}</td>
                                    <td class="num text-end strong">{{ \App\Support\Money::format($p->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel lg:col-span-7" aria-labelledby="bc-h">
            <div class="panel-head">
                <h2 id="bc-h" class="panel-title">أكبر العملاء</h2>
                <span class="text-2xs text-cream-3">أعلى 15</span>
            </div>
            @if($byCustomer->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش مبيعات في الفترة دي" />
            @else
                @php $cMax = max(1, $byCustomer->max('total')); @endphp
                <ul class="divide-y divide-navy-2">
                    @foreach($byCustomer as $row)
                        <li class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <a href="{{ route('admin.customers.show', $row->customer_id) }}" class="min-w-0 truncate py-1 text-sm text-cream hover:text-flame-ink-hi">{{ $row->customer?->name ?: '—' }}</a>
                                <span class="num shrink-0 text-sm font-semibold text-cream">{{ \App\Support\Money::short($row->total) }}</span>
                            </div>
                            {{-- The bar is the comparison; the number is the fact. --}}
                            <div class="mt-2 flex items-center gap-3">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-navy-2">
                                    <div class="h-full rounded-full bg-flame/70" style="width:{{ round($row->total / $cMax * 100) }}%"></div>
                                </div>
                                <span class="num shrink-0 text-2xs text-cream-3">{{ $row->n }} أوردر</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel lg:col-span-5" aria-labelledby="bg-h">
            <div class="panel-head"><h2 id="bg-h" class="panel-title">المبيعات بالمحافظة</h2></div>
            @if($byGovernorate->isEmpty())
                <x-empty class="m-4 border-0" title="مفيش بيانات" />
            @else
                @php $gMax = max(1, $byGovernorate->max('total')); @endphp
                <ul class="divide-y divide-navy-2">
                    @foreach($byGovernorate as $g)
                        <li class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="truncate text-sm text-cream-2">{{ $g->g }}</span>
                                <span class="num shrink-0 text-sm text-cream">{{ \App\Support\Money::short($g->total) }}</span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-navy-2">
                                <div class="h-full rounded-full bg-frost/60" style="width:{{ round($g->total / $gMax * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel p-5 lg:col-span-12" aria-labelledby="lead-h">
            <h2 id="lead-h" class="text-sm font-semibold text-cream">طلبات الموقع في الفترة</h2>
            <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach([
                    ['وصل', $leadStats['total'], 'text-cream'],
                    ['اتحوّل لعملاء', $leadStats['won'], 'text-good'],
                    ['لسه مفتوح', $leadStats['open'], 'text-flame-ink'],
                    ['ضاع', $leadStats['lost'], 'text-cream-3'],
                ] as [$label, $value, $tone])
                    <div>
                        <dt class="text-2xs text-cream-3">{{ $label }}</dt>
                        <dd class="num mt-1 text-xl font-semibold {{ $tone }}">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
@endsection
