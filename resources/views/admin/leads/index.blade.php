@extends('layouts.admin')

@section('title', 'طلبات الموقع')
@section('subtitle', $leads->total().' طلب')

@section('actions')
    <button type="button" data-sheet-open="lead-filters" class="btn btn-ghost btn-sm lg:hidden">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
        فلترة
    </button>
@endsection

@section('content')
    @php
        $tabs = [
            '' => ['كل الطلبات', $leads->total()],
            'new' => ['جديد', $counts['new'] ?? 0],
            'contacted' => ['اتكلّمنا', $counts['contacted'] ?? 0],
            'quoted' => ['اتبعت عرض', $counts['quoted'] ?? 0],
            'won' => ['اتحوّل', $counts['won'] ?? 0],
            'lost' => ['ضاع', $counts['lost'] ?? 0],
        ];
        $active = request('status', '');
    @endphp

    {{-- Status is a rail, not a dropdown: it is the filter people change most,
         and the counts double as the pipeline at a glance. --}}
    <nav class="-mx-4 flex gap-2 overflow-x-auto no-scrollbar px-4 pb-1 lg:mx-0 lg:px-0" aria-label="فلترة بالحالة">
        @foreach($tabs as $key => [$label, $count])
            <a href="{{ request()->fullUrlWithQuery(['status' => $key ?: null, 'page' => null]) }}"
               @class([
                   'flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs transition-colors',
                   'border-flame/50 bg-flame/12 text-cream' => $active === $key,
                   'border-navy-2 text-cream-2 hover:border-navy-3 hover:text-cream' => $active !== $key,
               ])>
                <span>{{ $label }}</span>
                <span class="num text-2xs {{ $active === $key ? 'text-flame-ink-hi' : 'text-cream-3' }}">{{ $count }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" data-auto-filter class="mt-4 hidden gap-2 lg:flex">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="دوّر باسم، منشأة، موبايل…" class="field max-w-xs" aria-label="بحث في الطلبات">
        <select name="assigned" class="field max-w-[11rem]" aria-label="فلترة بالمسؤول">
            <option value="">كل المسؤولين</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(request('assigned') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <select name="source" class="field max-w-[11rem]" aria-label="فلترة بالمصدر">
            <option value="">كل المصادر</option>
            <option value="landing_form" @selected(request('source') === 'landing_form')>فورم الموقع</option>
            <option value="blog_form" @selected(request('source') === 'blog_form')>فورم المدوّنة</option>
            <option value="manual" @selected(request('source') === 'manual')>إدخال يدوي</option>
        </select>
        @if(request()->hasAny(['q', 'assigned', 'source']))
            <a href="{{ route('admin.leads.index', ['status' => request('status')]) }}" class="btn btn-ghost btn-sm">مسح</a>
        @endif
    </form>

    @if($leads->isEmpty())
        <x-empty class="mt-6" title="مفيش طلبات هنا"
                 hint="لما حد يملأ فورم عرض السعر على الموقع، هيظهر في المكان ده فورًا ومعاه المحافظة والكمية اللي كتبها." />
    @else
        {{-- Phone: cards you can act on with one thumb. --}}
        <ul class="mt-4 flex flex-col gap-2.5 lg:hidden">
            @foreach($leads as $lead)
                <li class="panel p-4">
                    <a href="{{ route('admin.leads.show', $lead) }}" class="block">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-cream">{{ $lead->business_name ?: $lead->name }}</p>
                                <p class="mt-0.5 truncate text-2xs text-cream-3">{{ $lead->name }}</p>
                            </div>
                            <span class="badge shrink-0 {{ match($lead->status) { 'new' => 'badge-flame', 'won' => 'badge-good', 'lost' => 'badge-idle', default => 'badge-frost' } }}">{{ $lead->statusLabel() }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-2xs text-cream-3">
                            <span class="num">{{ $lead->phone }}</span>
                            @if($lead->governorate)<span>{{ $lead->governorate }}</span>@endif
                            @if($lead->monthly_volume)<span>{{ $lead->monthly_volume }}</span>@endif
                            <span>{{ $lead->created_at->diffForHumans(short: true) }}</span>
                        </div>
                    </a>
                    <div class="mt-3 flex gap-2">
                        <a href="tel:{{ $lead->phone }}" class="btn btn-ghost btn-sm flex-1">اتصال</a>
                        <a href="{{ $lead->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-solid btn-sm flex-1">واتساب</a>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="panel mt-4 hidden overflow-x-auto lg:block">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>المنشأة</th><th>الموبايل</th><th>المحافظة</th><th>الكمية</th>
                        <th>الحالة</th><th>المسؤول</th><th>وصل</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                        <tr>
                            <td>
                                <a href="{{ route('admin.leads.show', $lead) }}" class="strong hover:text-flame-ink-hi">{{ $lead->business_name ?: $lead->name }}</a>
                                <span class="block text-2xs text-cream-3">{{ $lead->name }}</span>
                            </td>
                            <td><span class="num">{{ $lead->phone }}</span></td>
                            <td>{{ $lead->governorate ?: '—' }}</td>
                            <td>{{ $lead->monthly_volume ?: '—' }}</td>
                            <td><span class="badge {{ match($lead->status) { 'new' => 'badge-flame', 'won' => 'badge-good', 'lost' => 'badge-idle', default => 'badge-frost' } }}">{{ $lead->statusLabel() }}</span></td>
                            <td>{{ $lead->assignee?->name ?: '—' }}</td>
                            <td class="whitespace-nowrap text-2xs text-cream-3">{{ $lead->created_at->diffForHumans(short: true) }}</td>
                            <td class="text-end">
                                <a href="{{ $lead->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm btn-icon" aria-label="واتساب {{ $lead->name }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $leads->links() }}</div>
    @endif
@endsection

@push('sheets')
    <dialog class="sheet lg:hidden" data-sheet="lead-filters" aria-labelledby="lf-h">
        <div class="sheet-panel">
            <div class="sheet-grip" aria-hidden="true"></div>
            <h2 id="lf-h" class="pb-4 text-base font-semibold text-cream">فلترة الطلبات</h2>

            <form method="GET" class="flex flex-col gap-4">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div>
                    <label for="f-q" class="label">بحث</label>
                    <input id="f-q" type="search" name="q" value="{{ request('q') }}" placeholder="اسم، منشأة، موبايل…" class="field">
                </div>
                <div>
                    <label for="f-assigned" class="label">المسؤول</label>
                    <select id="f-assigned" name="assigned" class="field">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('assigned') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2.5 pt-1">
                    <button type="submit" class="btn btn-primary flex-1">طبّق</button>
                    <a href="{{ route('admin.leads.index') }}" class="btn btn-ghost flex-1">مسح الكل</a>
                </div>
            </form>
        </div>
    </dialog>
@endpush
