@extends('layouts.admin')

@section('title', 'المقالات')
@section('subtitle', $posts->total().' مقال')

@section('actions')
    @if(auth()->user()->can_('blog.edit'))
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            <span class="max-sm:sr-only">مقال جديد</span>
        </a>
    @endif
@endsection

@section('content')
    @php $active = request('status', ''); @endphp

    <nav class="-mx-4 flex gap-2 overflow-x-auto no-scrollbar px-4 pb-1 lg:mx-0 lg:px-0" aria-label="فلترة بالحالة">
        @foreach(['' => ['الكل', $posts->total()]] + collect(\App\Models\Post::STATUSES)->map(fn ($l, $k) => [$l, $counts[$k] ?? 0])->all() as $key => [$label, $count])
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

    <form method="GET" data-auto-filter class="mt-4 flex flex-wrap gap-2">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="دوّر بعنوان…" class="field max-w-xs" aria-label="بحث في المقالات">
        <select name="category" class="field max-w-[13rem]" aria-label="فلترة بالقسم">
            <option value="">كل الأقسام</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </form>

    @if($posts->isEmpty())
        <x-empty class="mt-6" title="مفيش مقالات هنا"
                 hint="كل مقال بيتنشر بيتضاف تلقائيًا لخريطة الموقع وللـ RSS، وبيظهر في «اقرأ كمان» في باقي المقالات."
                 :action="auth()->user()->can_('blog.edit') ? 'اكتب أول مقال' : null"
                 :href="auth()->user()->can_('blog.edit') ? route('admin.posts.create') : null" />
    @else
        <ul class="mt-4 flex flex-col gap-2.5">
            @foreach($posts as $post)
                <li class="panel flex items-start gap-4 p-4">
                    <div class="hidden aspect-4/3 w-24 shrink-0 overflow-clip rounded-lg border border-navy-2 bg-navy-2 sm:block">
                        @if($post->cover_image)
                            <img src="{{ $post->cover_image }}" alt="" loading="lazy" class="size-full object-cover" width="96" height="72">
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge {{ match($post->status) { 'published' => 'badge-good', 'scheduled' => 'badge-frost', default => 'badge-idle' } }}">{{ $post->statusLabel() }}</span>
                            @if($post->category)<span class="text-2xs text-cream-3">{{ $post->category->name }}</span>@endif
                            @if($post->noindex)<span class="badge badge-bad">مستبعد من جوجل</span>@endif
                        </div>

                        <a href="{{ route('admin.posts.edit', $post) }}" class="mt-1 block py-1 text-sm font-medium text-cream transition-colors hover:text-flame-ink-hi">{{ $post->title }}</a>

                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-2xs text-cream-3">
                            <span>{{ $post->author?->name ?: '—' }}</span>
                            <span class="num">{{ ($post->published_at ?: $post->updated_at)->format('Y-m-d') }}</span>
                            <span><span class="num">{{ $post->reading_minutes }}</span> د</span>
                            <span><span class="num">{{ $post->views }}</span> قراءة</span>
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        {{-- The SEO score is the reason to open a post again. --}}
                        <span @class([
                            'num grid size-10 place-items-center rounded-lg border text-sm font-semibold',
                            'border-good/40 bg-good/10 text-good' => $post->seo_score >= 80,
                            'border-warn/40 bg-warn/10 text-warn' => $post->seo_score >= 55 && $post->seo_score < 80,
                            'border-bad/40 bg-bad/10 text-bad' => $post->seo_score < 55,
                        ]) title="درجة السيو">{{ $post->seo_score }}</span>

                        @if($post->isLive())
                            <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="inline-block py-1 text-2xs text-cream-3 transition-colors hover:text-flame-ink-hi">عرض ↗</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-5">{{ $posts->links() }}</div>
    @endif
@endsection
