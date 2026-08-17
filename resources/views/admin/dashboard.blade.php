@extends('layouts.admin')

@section('title', 'لوحة اليوم')
@section('subtitle', now()->translatedFormat('l j F Y'))

@section('actions')
    @if(auth()->user()->can_('orders.edit'))
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">أوردر جديد</span>
        </a>
    @endif
@endsection

@section('content')
    @php $max = max(1, collect($series)->max('value')); @endphp

    <div class="grid gap-4 lg:grid-cols-12">

        {{-- The number this dashboard exists for. It gets the most ink on the
             page, and it links straight to the list of who owes what. --}}
        <section class="panel relative overflow-clip p-5 lg:col-span-5 lg:p-6">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(120%_90%_at_100%_0%,var(--flame),transparent_62%)] opacity-[0.13]" aria-hidden="true"></div>

            <p class="text-xs text-cream-3">فلوس عند العملاء</p>
            <p class="mt-3 flex items-baseline gap-2 leading-none">
                <span class="num text-[clamp(2.1rem,1.4rem+3vw,3.2rem)] font-semibold {{ $stats['receivable'] > 0 ? 'text-flame-ink' : 'text-good' }}">{{ \App\Support\Money::format($stats['receivable']) }}</span>
                <span class="text-sm text-cream-3">{{ $currency }}</span>
            </p>

            <div class="mt-5 grid grid-cols-2 gap-4 border-t border-navy-2 pt-4">
                <div>
                    <p class="text-2xs text-cream-3">اتحصّل الشهر ده</p>
                    <p class="num mt-1 text-lg font-semibold text-good">{{ \App\Support\Money::short($stats['collected_month']) }}</p>
                </div>
                <div>
                    <p class="text-2xs text-cream-3">عملاء نشطين</p>
                    <p class="num mt-1 text-lg font-semibold text-cream">{{ $stats['customers'] }}</p>
                </div>
            </div>

            @if(auth()->user()->can_('reports.view'))
                <a href="{{ route('admin.reports.receivables') }}" class="btn btn-ghost btn-sm mt-5 w-full">
                    شوف مين عليه إيه
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
                </a>
            @endif
        </section>

        {{-- 12-month sales, drawn with plain elements — no chart library, and
             every bar carries its own accessible label. --}}
        <section class="panel p-5 lg:col-span-7 lg:p-6" aria-labelledby="chart-h">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="chart-h" class="text-sm font-semibold text-cream">المبيعات — آخر 12 شهر</h2>
                    <p class="mt-1 flex items-baseline gap-1.5">
                        <span class="num text-2xl font-semibold text-cream">{{ \App\Support\Money::short($stats['sales_month']) }}</span>
                        <span class="text-2xs text-cream-3">{{ $currency }} · الشهر ده</span>
                    </p>
                </div>
                @if($stats['sales_delta'] !== null)
                    <p @class([
                        'flex items-center gap-1 text-xs',
                        'text-good' => $stats['sales_delta'] > 0,
                        'text-bad' => $stats['sales_delta'] < 0,
                        'text-cream-3' => $stats['sales_delta'] === 0,
                    ])>
                        <span aria-hidden="true">{{ $stats['sales_delta'] > 0 ? '↑' : ($stats['sales_delta'] < 0 ? '↓' : '→') }}</span>
                        <span class="num">{{ abs($stats['sales_delta']) }}%</span>
                        <span>عن الشهر اللي فات</span>
                    </p>
                @endif
            </div>

            {{-- items-stretch, not items-end: the bars are sized in percent, and
                 a percentage height only resolves against a parent whose own
                 height is definite. With items-end each <li> collapses to its
                 content and every bar renders at zero. --}}
            <ul class="mt-6 flex h-32 items-stretch gap-1.5" role="list">
                @foreach($series as $i => $point)
                    @php $h = $max > 0 ? max(2, round($point['value'] / $max * 100)) : 2; @endphp
                    <li class="group flex flex-1 flex-col items-center justify-end gap-2">
                        <span class="num text-2xs text-cream-3 opacity-0 transition-opacity group-hover:opacity-100 max-sm:hidden">{{ \App\Support\Money::short($point['value']) }}</span>
                        {{-- `block` is load-bearing: a percentage height does
                             nothing on an inline element. --}}
                        <span class="block w-full rounded-t-[3px] transition-colors {{ $i === count($series) - 1 ? 'bg-flame' : 'bg-navy-3 group-hover:bg-steel' }}"
                              style="height:{{ $h }}%"
                              title="{{ $point['key'] }} — {{ \App\Support\Money::format($point['value']) }} {{ $currency }}"></span>
                    </li>
                @endforeach
            </ul>
            <ul class="mt-2 flex gap-1.5 text-2xs text-cream-3" aria-hidden="true">
                @foreach($series as $point)
                    <li class="num flex-1 text-center">{{ $point['short'] }}</li>
                @endforeach
            </ul>

            {{-- The same series as text, for anyone the bars do not reach. --}}
            <table class="sr-only">
                <caption>المبيعات الشهرية</caption>
                <thead><tr><th>الشهر</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    @foreach($series as $point)
                        <tr><td>{{ $point['key'] }}</td><td>{{ \App\Support\Money::format($point['value']) }} {{ $currency }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- One instrument strip instead of a row of identical cards. --}}
        <section class="panel grid grid-cols-2 divide-navy-2 lg:col-span-12 lg:grid-cols-4 lg:divide-x lg:divide-x-reverse">
            @php
                $strip = array_values(array_filter([
                    auth()->user()->can_('leads.view') ? ['طلبات موقع جديدة', $stats['new_leads'], 'مستنية رد', route('admin.leads.index').'?status=new', $stats['new_leads'] > 0] : null,
                    auth()->user()->can_('leads.view') ? ['طلبات مفتوحة', $stats['open_leads'], 'في الخط', route('admin.leads.index'), false] : null,
                    auth()->user()->can_('orders.view') ? ['أوردرات الشهر', $stats['orders_month'], 'مؤكّدة أو متسلّمة', route('admin.orders.index'), false] : null,
                    auth()->user()->can_('payments.view') ? ['متوسط الأوردر', $stats['orders_month'] > 0 ? \App\Support\Money::short($stats['sales_month'] / $stats['orders_month']) : '0', $currency, null, false] : null,
                ]));
            @endphp
            @foreach($strip as [$label, $value, $hint, $href, $hot])
                <div class="border-b border-navy-2 p-4 last:border-b-0 lg:border-b-0">
                    @if($href)<a href="{{ $href }}" class="block">@endif
                        <p class="text-2xs text-cream-3">{{ $label }}</p>
                        <p class="num mt-1.5 text-xl font-semibold {{ $hot ? 'text-flame-ink' : 'text-cream' }}">{{ $value }}</p>
                        <p class="mt-0.5 text-2xs text-cream-3">{{ $hint }}</p>
                    @if($href)</a>@endif
                </div>
            @endforeach
        </section>

        {{-- Leads: the queue someone has to work through today. --}}
        @if($recentLeads->isNotEmpty() || auth()->user()->can_('leads.view'))
            <section class="panel lg:col-span-7" aria-labelledby="leads-h">
                <div class="panel-head">
                    <h2 id="leads-h" class="panel-title">أحدث طلبات الموقع</h2>
                    <a href="{{ route('admin.leads.index') }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">كلهم ←</a>
                </div>

                @if($recentLeads->isEmpty())
                    <x-empty class="m-4 border-0" title="مفيش طلبات لسه"
                             hint="أول ما حد يبعت من فورم الموقع هيظهر هنا على طول." />
                @else
                    <ul class="divide-y divide-navy-2">
                        @foreach($recentLeads as $lead)
                            <li>
                                <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-navy/50">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="truncate text-sm font-medium text-cream">{{ $lead->business_name ?: $lead->name }}</span>
                                            <span class="badge {{ $lead->status === 'new' ? 'badge-flame' : ($lead->status === 'won' ? 'badge-good' : ($lead->status === 'lost' ? 'badge-idle' : 'badge-frost')) }}">{{ $lead->statusLabel() }}</span>
                                        </span>
                                        <span class="mt-0.5 flex flex-wrap items-center gap-x-2 text-2xs text-cream-3">
                                            <span class="num">{{ $lead->phone }}</span>
                                            @if($lead->governorate)<span aria-hidden="true">·</span><span>{{ $lead->governorate }}</span>@endif
                                            @if($lead->monthly_volume)<span aria-hidden="true">·</span><span>{{ $lead->monthly_volume }}</span>@endif
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-2xs text-cream-3">{{ $lead->created_at->diffForHumans(short: true) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        {{-- Debtors, biggest first. --}}
        @if(auth()->user()->can_('customers.view'))
            <section class="panel lg:col-span-5" aria-labelledby="debt-h">
                <div class="panel-head">
                    <h2 id="debt-h" class="panel-title">أكبر المديونيات</h2>
                    @if(auth()->user()->can_('reports.view'))
                        <a href="{{ route('admin.reports.receivables') }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">التقرير ←</a>
                    @endif
                </div>

                @if($topDebtors->isEmpty())
                    <x-empty class="m-4 border-0" title="مفيش مديونيات" hint="كل العملاء مسدّدين. حالة كويسة." />
                @else
                    <ul class="divide-y divide-navy-2">
                        @foreach($topDebtors as $c)
                            <li>
                                <a href="{{ route('admin.customers.show', $c) }}" class="flex items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-navy/50">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm text-cream">{{ $c->name }}</span>
                                        <span class="num block text-2xs text-cream-3">{{ $c->code }}</span>
                                    </span>
                                    <span class="shrink-0 text-end">
                                        <span class="num block text-sm font-semibold {{ $c->overCreditLimit() ? 'text-bad' : 'text-flame-ink' }}">{{ \App\Support\Money::short($c->computed_balance) }}</span>
                                        @if($c->overCreditLimit())
                                            <span class="text-2xs text-bad">فوق الحد</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        {{-- Orders. On phones the table collapses into rows you can actually read. --}}
        @if(auth()->user()->can_('orders.view'))
            <section class="panel lg:col-span-8" aria-labelledby="orders-h">
                <div class="panel-head">
                    <h2 id="orders-h" class="panel-title">آخر الأوردرات</h2>
                    <a href="{{ route('admin.orders.index') }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">كلهم ←</a>
                </div>

                @if($recentOrders->isEmpty())
                    <x-empty class="m-4 border-0" title="لسه مفيش أوردرات"
                             hint="سجّل أول أوردر وهتلاقي الحسابات بتتحرّك لوحدها."
                             :action="auth()->user()->can_('orders.edit') ? 'أوردر جديد' : null"
                             :href="auth()->user()->can_('orders.edit') ? route('admin.orders.create') : null" />
                @else
                    <ul class="divide-y divide-navy-2">
                        @foreach($recentOrders as $order)
                            <li>
                                <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-navy/50">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="num text-2xs text-cream-3">{{ $order->code }}</span>
                                            <span class="truncate text-sm text-cream">{{ $order->customer?->name }}</span>
                                        </span>
                                        <span class="mt-0.5 flex items-center gap-2 text-2xs text-cream-3">
                                            <span class="num">{{ $order->order_date->format('Y-m-d') }}</span>
                                            <span class="badge {{ match($order->paymentStatus()) { 'paid' => 'badge-good', 'partial' => 'badge-warn', 'unpaid' => 'badge-bad', default => 'badge-idle' } }}">{{ $order->paymentStatusLabel() }}</span>
                                        </span>
                                    </span>
                                    <span class="num shrink-0 text-sm font-semibold text-cream">{{ \App\Support\Money::short($order->total) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        @if(auth()->user()->can_('blog.view'))
            <section class="panel lg:col-span-4" aria-labelledby="drafts-h">
                <div class="panel-head">
                    <h2 id="drafts-h" class="panel-title">مقالات مش منشورة</h2>
                    <a href="{{ route('admin.posts.index') }}" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">المدوّنة ←</a>
                </div>

                @if($draftPosts->isEmpty())
                    <x-empty class="m-4 border-0" title="مفيش مسودّات"
                             :action="auth()->user()->can_('blog.edit') ? 'اكتب مقال' : null"
                             :href="auth()->user()->can_('blog.edit') ? route('admin.posts.create') : null" />
                @else
                    <ul class="divide-y divide-navy-2">
                        @foreach($draftPosts as $post)
                            <li>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-navy/50">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-cream">{{ $post->title }}</span>
                                        <span class="mt-0.5 flex items-center gap-2 text-2xs text-cream-3">
                                            <span class="badge {{ $post->status === 'scheduled' ? 'badge-frost' : 'badge-idle' }}">{{ $post->statusLabel() }}</span>
                                            <span>سيو <span class="num">{{ $post->seo_score }}</span></span>
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    </div>
@endsection
