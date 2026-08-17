@extends('layouts.admin')

@section('title', 'العملاء')
@section('subtitle', $customers->total().' عميل')

@section('actions')
    <button type="button" data-sheet-open="cust-filters" class="btn btn-ghost btn-sm lg:hidden">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
        فلترة
    </button>
    @if(auth()->user()->can_('customers.edit'))
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">عميل جديد</span>
        </a>
    @endif
@endsection

@section('content')
    <form method="GET" data-auto-filter class="hidden flex-wrap gap-2 lg:flex">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="دوّر باسم، كود، موبايل…" class="field max-w-xs" aria-label="بحث في العملاء">
        <select name="type" class="field max-w-[11rem]" aria-label="فلترة بالنشاط">
            <option value="">كل الأنشطة</option>
            @foreach(\App\Models\Customer::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="governorate" class="field max-w-[11rem]" aria-label="فلترة بالمحافظة">
            <option value="">كل المحافظات</option>
            @foreach($governorates as $g)
                <option value="{{ $g }}" @selected(request('governorate') === $g)>{{ $g }}</option>
            @endforeach
        </select>
        <select name="sort" class="field max-w-[11rem]" aria-label="ترتيب القايمة">
            <option value="name" @selected(request('sort', 'name') === 'name')>بالاسم</option>
            <option value="debt" @selected(request('sort') === 'debt')>الأكتر مديونية</option>
            <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
        </select>
        <select name="state" class="field max-w-[9rem]" aria-label="فلترة بحالة العميل">
            <option value="">النشطين</option>
            <option value="inactive" @selected(request('state') === 'inactive')>الموقوفين</option>
        </select>
    </form>

    @if($customers->isEmpty())
        <x-empty class="mt-6" title="مفيش عملاء مطابقين"
                 hint="ابدأ بتحويل طلب من طلبات الموقع، أو سجّل عميل يدوي لو التعامل بدأ برّه الموقع."
                 :action="auth()->user()->can_('customers.edit') ? 'عميل جديد' : null"
                 :href="auth()->user()->can_('customers.edit') ? route('admin.customers.create') : null" />
    @else
        <ul class="mt-4 flex flex-col gap-2.5 lg:hidden">
            @foreach($customers as $c)
                @php $balance = $c->balance(); @endphp
                <li class="panel p-4">
                    <a href="{{ route('admin.customers.show', $c) }}" class="block">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-cream">{{ $c->name }}</p>
                                <p class="mt-0.5 flex items-center gap-2 text-2xs text-cream-3">
                                    <span class="num">{{ $c->code }}</span>
                                    <span>{{ $c->typeLabel() }}</span>
                                    @if($c->governorate)<span>{{ $c->governorate }}</span>@endif
                                </p>
                            </div>
                            <div class="shrink-0 text-end">
                                <p class="num text-sm font-semibold {{ $balance > 0.005 ? ($c->overCreditLimit() ? 'text-bad' : 'text-flame-ink') : 'text-good' }}">{{ \App\Support\Money::short($balance) }}</p>
                                <p class="text-2xs text-cream-3">{{ $balance > 0.005 ? 'عليه' : ($balance < -0.005 ? 'ليه' : 'مسدّد') }}</p>
                            </div>
                        </div>
                    </a>
                    <div class="mt-3 flex gap-2">
                        <a href="tel:{{ $c->phone }}" class="btn btn-ghost btn-sm flex-1">اتصال</a>
                        <a href="{{ $c->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm flex-1">واتساب</a>
                        <a href="{{ route('admin.customers.statement', $c) }}" class="btn btn-solid btn-sm flex-1">كشف حساب</a>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="panel mt-4 hidden overflow-x-auto lg:block">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>الكود</th><th>المنشأة</th><th>النشاط</th><th>المحافظة</th><th>الموبايل</th>
                        <th class="text-end">مبيعات</th><th class="text-end">تحصيل</th><th class="text-end">الرصيد</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $c)
                        @php $balance = $c->balance(); @endphp
                        <tr>
                            <td><span class="num text-2xs text-cream-3">{{ $c->code }}</span></td>
                            <td>
                                <a href="{{ route('admin.customers.show', $c) }}" class="strong hover:text-flame-ink-hi">{{ $c->name }}</a>
                                @if($c->contact_name)<span class="block text-2xs text-cream-3">{{ $c->contact_name }}</span>@endif
                            </td>
                            <td>{{ $c->typeLabel() }}</td>
                            <td>{{ $c->governorate ?: '—' }}</td>
                            <td><span class="num">{{ $c->phone }}</span></td>
                            <td class="num text-end">{{ \App\Support\Money::short($c->totalBilled()) }}</td>
                            <td class="num text-end text-good">{{ \App\Support\Money::short($c->totalPaid()) }}</td>
                            <td class="text-end">
                                <span class="num font-semibold {{ $balance > 0.005 ? ($c->overCreditLimit() ? 'text-bad' : 'text-flame-ink') : 'text-good' }}">{{ \App\Support\Money::short($balance) }}</span>
                                @if($c->overCreditLimit())<span class="block text-2xs text-bad">فوق الحد</span>@endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.customers.statement', $c) }}" class="btn btn-ghost btn-sm">كشف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $customers->links() }}</div>
    @endif
@endsection

@push('sheets')
    <dialog class="sheet lg:hidden" data-sheet="cust-filters" aria-labelledby="cf-h">
        <div class="sheet-panel">
            <div class="sheet-grip" aria-hidden="true"></div>
            <h2 id="cf-h" class="pb-4 text-base font-semibold text-cream">فلترة العملاء</h2>
            <form method="GET" class="flex flex-col gap-4">
                <div>
                    <label for="cf-q" class="label">بحث</label>
                    <input id="cf-q" type="search" name="q" value="{{ request('q') }}" class="field" placeholder="اسم، كود، موبايل…">
                </div>
                <div>
                    <label for="cf-type" class="label">النشاط</label>
                    <select id="cf-type" name="type" class="field">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Customer::TYPES as $key => $label)
                            <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cf-sort" class="label">الترتيب</label>
                    <select id="cf-sort" name="sort" class="field">
                        <option value="name" @selected(request('sort', 'name') === 'name')>بالاسم</option>
                        <option value="debt" @selected(request('sort') === 'debt')>الأكتر مديونية</option>
                        <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
                    </select>
                </div>
                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="btn btn-primary flex-1">طبّق</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost flex-1">مسح</a>
                </div>
            </form>
        </div>
    </dialog>
@endpush
