@extends('layouts.admin')

@section('title', 'كشف حساب')
@section('subtitle', $customer->name.' · '.$customer->code)
@section('back', route('admin.customers.show', $customer))

@section('actions')
    <button type="button" onclick="window.print()" class="btn btn-ghost btn-sm no-print">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4v-6h16v6h-2M8 14h8v7H8z"/></svg>
        طباعة
    </button>
@endsection

@section('content')
    <form method="GET" data-auto-filter class="no-print flex flex-wrap items-end gap-3">
        <div>
            <label for="from" class="label">من</label>
            <input id="from" type="date" name="from" value="{{ $from?->toDateString() }}" class="field num">
        </div>
        <div>
            <label for="to" class="label">إلى</label>
            <input id="to" type="date" name="to" value="{{ $to?->toDateString() }}" class="field num">
        </div>
        @if($from || $to)
            <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-ghost btn-sm">من الأول</a>
        @endif
    </form>

    {{-- Print header: the sheet has to identify itself once it leaves the screen. --}}
    <div class="mt-5 hidden print:block">
        <p class="text-lg font-semibold">زيلاند — كشف حساب</p>
        <p class="mt-1 text-sm">{{ $customer->name }} ({{ $customer->code }}) · {{ $customer->phone }}</p>
        <p class="text-sm">
            {{ $from ? $from->format('Y-m-d') : 'من البداية' }} — {{ $to ? $to->format('Y-m-d') : now()->format('Y-m-d') }}
            · طُبع {{ now()->format('Y-m-d') }}
        </p>
    </div>

    <div class="panel mt-5 print-plain">
        <div class="panel-head">
            <h2 class="panel-title">الحركة</h2>
            <p class="text-2xs text-cream-3">
                رصيد أول المدة <span class="num text-cream-2">{{ \App\Support\Money::format($totals['opening']) }}</span> {{ $currency }}
            </p>
        </div>

        @if($rows->isEmpty())
            <x-empty class="m-4 border-0" title="مفيش حركة في الفترة دي"
                     hint="جرّب توسّع المدى الزمني، أو شيل الفلتر خالص." />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>التاريخ</th><th>المستند</th><th>البيان</th>
                            <th class="text-end">مدين</th><th class="text-end">دائن</th><th class="text-end">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-cream-3">رصيد أول المدة</td>
                            <td class="num text-end strong">{{ \App\Support\Money::format($totals['opening']) }}</td>
                        </tr>
                        @foreach($rows as $row)
                            <tr>
                                <td class="num whitespace-nowrap">{{ $row['date']->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ $row['url'] }}" class="num text-2xs text-cream-3 hover:text-flame-ink-hi">{{ $row['ref'] }}</a>
                                </td>
                                <td>{{ $row['label'] }}</td>
                                <td class="num text-end">{{ $row['debit'] > 0 ? \App\Support\Money::format($row['debit']) : '—' }}</td>
                                <td class="num text-end text-good print:text-black">{{ $row['credit'] > 0 ? \App\Support\Money::format($row['credit']) : '—' }}</td>
                                <td class="num text-end strong">{{ \App\Support\Money::format($row['balance']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-navy-3">
                            <td colspan="3" class="strong">الإجمالي</td>
                            <td class="num text-end strong">{{ \App\Support\Money::format($totals['debit']) }}</td>
                            <td class="num text-end strong">{{ \App\Support\Money::format($totals['credit']) }}</td>
                            <td class="num text-end strong {{ $totals['closing'] > 0.005 ? 'text-flame-ink print:text-black' : 'text-good print:text-black' }}">{{ \App\Support\Money::format($totals['closing']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <div class="panel print-plain p-4">
            <p class="text-2xs text-cream-3">إجمالي المستحق عليه</p>
            <p class="num mt-1.5 text-xl font-semibold text-cream">{{ \App\Support\Money::format($totals['debit'] + $totals['opening']) }}</p>
        </div>
        <div class="panel print-plain p-4">
            <p class="text-2xs text-cream-3">إجمالي المسدّد</p>
            <p class="num mt-1.5 text-xl font-semibold text-good print:text-black">{{ \App\Support\Money::format($totals['credit']) }}</p>
        </div>
        <div class="panel print-plain p-4">
            <p class="text-2xs text-cream-3">{{ $totals['closing'] > 0.005 ? 'الباقي عليه' : 'الرصيد' }}</p>
            <p class="num mt-1.5 text-xl font-semibold {{ $totals['closing'] > 0.005 ? 'text-flame-ink print:text-black' : 'text-good print:text-black' }}">{{ \App\Support\Money::format($totals['closing']) }}</p>
        </div>
    </div>

    <p class="mt-6 hidden text-xs print:block">
        الكشف ده صادر من نظام زيلاند بتاريخ {{ now()->format('Y-m-d H:i') }}. لأي استفسار: {{ $settings['phone'] }}
    </p>
@endsection
