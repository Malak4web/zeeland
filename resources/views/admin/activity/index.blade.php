@extends('layouts.admin')

@section('title', 'سجل النشاط')
@section('subtitle', 'مين عمل إيه وامتى')

@section('content')
    <form method="GET" data-auto-filter class="flex flex-wrap gap-2">
        <select name="user" class="field max-w-[13rem]" aria-label="فلترة بالمستخدم">
            <option value="">كل المستخدمين</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user') == $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="action" class="field max-w-[11rem]" aria-label="فلترة بنوع الحركة">
            <option value="">كل الأنواع</option>
            @foreach(['created' => 'إضافة', 'updated' => 'تعديل', 'deleted' => 'مسح', 'login' => 'دخول', 'logout' => 'خروج'] as $key => $label)
                <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['user', 'action']))
            <a href="{{ route('admin.activity.index') }}" class="btn btn-ghost btn-sm">مسح</a>
        @endif
    </form>

    @if($activities->isEmpty())
        <x-empty class="mt-6" title="مفيش نشاط مسجّل" hint="أي إضافة أو تعديل أو مسح بيتسجّل هنا تلقائيًا." />
    @else
        <ol class="panel mt-4 divide-y divide-navy-2">
            @foreach($activities as $a)
                <li class="flex items-start gap-3 px-4 py-3">
                    <span @class([
                        'mt-1 size-2 shrink-0 rounded-full',
                        'bg-good' => $a->action === 'created',
                        'bg-frost' => $a->action === 'updated',
                        'bg-bad' => $a->action === 'deleted',
                        'bg-cream-3' => in_array($a->action, ['login', 'logout'], true),
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-cream-2">
                            <span class="text-cream">{{ $a->user?->name ?: 'النظام' }}</span>
                            {{ $a->description }}
                        </p>
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-2xs text-cream-3">
                            <span class="num">{{ $a->created_at->format('Y-m-d H:i') }}</span>
                            @if($a->subject_type)<span aria-hidden="true">·</span><span>{{ $a->subject_type }}</span>@endif
                            @if($a->ip)<span aria-hidden="true">·</span><span class="num">{{ $a->ip }}</span>@endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-5">{{ $activities->links() }}</div>
    @endif
@endsection
