@extends('layouts.site')

@section('body')
    @include('site.partials.nav')

    <main id="main">
        {{-- Heading. No eyebrow, no card: the title carries the page. --}}
        <section class="grain relative overflow-clip border-b border-navy-2">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[24rem] bg-[radial-gradient(closest-side,var(--flame),transparent_74%)] opacity-[calc(0.1*var(--bloom))]" aria-hidden="true"></div>
            <div class="container-zl py-[clamp(3rem,8vh,5rem)]">
                <h1 class="text-display max-w-[16ch] text-cream">{{ $heading }}</h1>
                @if($intro)
                    <p class="prose-zl mt-6 max-w-[58ch]">{{ $intro }}</p>
                @endif
            </div>
        </section>

        <div class="container-zl grid gap-x-14 gap-y-12 py-[clamp(3rem,7vh,4.5rem)] lg:grid-cols-12">

            {{-- Pinned rail: filters + the standing CTA. The list beside it is
                 always taller, so this column would otherwise be dead space. --}}
            <aside class="min-w-0 lg:col-span-3 lg:sticky lg:top-28 lg:self-start" aria-label="أقسام المدوّنة">
                <h2 class="text-[0.85rem] font-semibold text-cream-3">الأقسام</h2>
                <ul class="mt-4 flex gap-2 overflow-x-auto no-scrollbar pb-1 lg:flex-col lg:overflow-visible lg:pb-0">
                    <li class="shrink-0">
                        <a href="{{ route('blog.index') }}"
                           @class([
                               'block whitespace-nowrap rounded-full border px-4 py-2 text-[0.9rem] transition-colors lg:rounded-xl',
                               'border-flame/50 bg-flame/10 text-cream' => ! ($activeCategory ?? null),
                               'border-navy-2 text-cream-2 hover:border-navy-3 hover:text-cream' => ($activeCategory ?? null),
                           ])>كل المقالات</a>
                    </li>
                    @foreach($categories as $cat)
                        <li class="shrink-0">
                            <a href="{{ $cat->url() }}"
                               @class([
                                   'flex items-center justify-between gap-3 whitespace-nowrap rounded-full border px-4 py-2 text-[0.9rem] transition-colors lg:rounded-xl',
                                   'border-flame/50 bg-flame/10 text-cream' => ($activeCategory ?? null) === $cat->id,
                                   'border-navy-2 text-cream-2 hover:border-navy-3 hover:text-cream' => ($activeCategory ?? null) !== $cat->id,
                               ])>
                                <span>{{ $cat->name }}</span>
                                <span class="num text-[0.7rem] text-cream-3">{{ $cat->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8 rounded-2xl border border-navy-2 bg-navy/50 p-5 max-lg:hidden">
                    <p class="text-[0.95rem] leading-[1.9] text-cream-2">بتدوّر على مورّد بطاطس نص مقلية ثابت الجودة؟</p>
                    <a href="{{ route('home') }}#quote"
                       class="mt-4 block rounded-full bg-flame px-5 py-3 text-center text-[0.92rem] font-semibold text-on-flame transition-colors hover:bg-flame-hi">اطلب عرض سعر</a>
                </div>
            </aside>

            <div class="min-w-0 lg:col-span-9">
                @if($posts->isEmpty())
                    <div class="rounded-2xl border border-dashed border-navy-3 px-6 py-16 text-center">
                        <p class="text-h3 text-cream">لسه مفيش مقالات هنا</p>
                        <p class="prose-zl mx-auto mt-3 max-w-[40ch]">أول ما ننشر حاجة هتلاقيها في المكان ده.</p>
                        <a href="{{ route('home') }}#quote" class="mt-6 inline-block rounded-full bg-flame px-6 py-3 font-medium text-on-flame transition-colors hover:bg-flame-hi">اطلب عرض سعر</a>
                    </div>
                @else
                    @if($featured)
                        {{-- The lead story gets the picture; the rest get the list. --}}
                        <a href="{{ $featured->url() }}" class="group block overflow-clip rounded-[1.5rem] border border-navy-2 bg-navy/40 transition-colors duration-300 hover:border-navy-3">
                            <div class="grid md:grid-cols-12">
                                <div class="relative md:col-span-7">
                                    {{-- `aspect-auto` at md is load-bearing: once the height is
                                         definite (h-full), an aspect-ratio derives the WIDTH from
                                         it and the image spills out of its grid column. --}}
                                    <div class="aspect-16/10 w-full overflow-clip md:aspect-auto md:h-full">
                                        @if($featured->cover_image)
                                            <img src="{{ $featured->cover_image }}" alt="{{ $featured->cover_alt ?: $featured->title }}"
                                                 class="size-full object-cover transition-transform duration-700 ease-out-quart group-hover:scale-[1.03]" width="900" height="560">
                                        @else
                                            <div class="size-full bg-navy-2"></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col justify-center gap-4 p-6 md:col-span-5 md:p-8">
                                    <div class="flex flex-wrap items-center gap-2 text-[0.78rem] text-cream-3">
                                        @if($featured->category)<span class="rounded-full bg-flame/12 px-3 py-1 text-flame-ink-hi">{{ $featured->category->name }}</span>@endif
                                        <span class="num">{{ $featured->reading_minutes }}</span><span>دقيقة قراية</span>
                                    </div>
                                    <h2 class="text-h3 text-cream">{{ $featured->title }}</h2>
                                    @if($featured->excerpt)
                                        <p class="line-clamp-3 text-[0.98rem] leading-[1.9] text-cream-2">{{ $featured->excerpt }}</p>
                                    @endif
                                    <span class="text-[0.85rem] font-medium text-flame-ink-hi">اقرأ المقال ←</span>
                                </div>
                            </div>
                        </a>
                    @endif

                    {{-- An editorial list, not a card grid: hairline rows, thumb
                         at a fixed ratio, and the title doing the work. --}}
                    <ol class="mt-10 flex flex-col">
                        @foreach($posts as $i => $post)
                            @if($featured && $i === 0) @continue @endif
                            <li class="border-t border-navy-2 first:border-t-0">
                                <a href="{{ $post->url() }}" class="group grid gap-5 py-7 sm:grid-cols-12 sm:items-start">
                                    <div class="sm:col-span-4 lg:col-span-3">
                                        <div class="aspect-4/3 overflow-clip rounded-xl border border-navy-2 bg-navy-2">
                                            @if($post->cover_image)
                                                <img src="{{ $post->cover_image }}" alt="{{ $post->cover_alt ?: $post->title }}" loading="lazy"
                                                     class="size-full object-cover transition-transform duration-700 ease-out-quart group-hover:scale-[1.04]" width="400" height="300">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="sm:col-span-8 lg:col-span-9">
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.76rem] text-cream-3">
                                            @if($post->category)<span class="text-flame-ink-hi">{{ $post->category->name }}</span><span aria-hidden="true">·</span>@endif
                                            <span>{{ $post->published_at?->translatedFormat('j F Y') }}</span>
                                            <span aria-hidden="true">·</span>
                                            <span><span class="num">{{ $post->reading_minutes }}</span> دقيقة</span>
                                        </div>
                                        <h2 class="mt-2 text-[1.25rem] leading-[1.5] font-semibold text-cream transition-colors duration-300 group-hover:text-flame-ink-hi sm:text-[1.4rem]">{{ $post->title }}</h2>
                                        @if($post->excerpt)
                                            <p class="mt-2 line-clamp-2 max-w-[62ch] text-[0.95rem] leading-[1.9] text-cream-2">{{ $post->excerpt }}</p>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-10">{{ $posts->links() }}</div>
                @endif

                <div class="mt-12 rounded-2xl border border-navy-2 bg-navy/50 p-6 lg:hidden">
                    <p class="text-[0.98rem] leading-[1.9] text-cream-2">بتدوّر على مورّد بطاطس نص مقلية ثابت الجودة؟</p>
                    <a href="{{ route('home') }}#quote"
                       class="mt-4 block rounded-full bg-flame px-5 py-3.5 text-center font-semibold text-on-flame">اطلب عرض سعر</a>
                </div>
            </div>
        </div>
    </main>

    @include('site.partials.footer')
@endsection
