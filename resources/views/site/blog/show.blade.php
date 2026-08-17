@php $toc = \App\Support\Toc::build($post->body); @endphp

@extends('layouts.site')

@push('head')
    @if($post->cover_image)
        <link rel="preload" as="image" href="{{ $post->cover_image }}" fetchpriority="high">
    @endif
@endpush

@section('body')
    {{-- Reading progress: the one piece of motion on this page, and it reports
         state rather than decorating. --}}
    <div class="fixed inset-x-0 top-0 z-30 h-[3px] bg-transparent" aria-hidden="true">
        <div data-reading-progress class="h-full w-0 origin-[right] bg-flame transition-[width] duration-100 ease-linear"></div>
    </div>

    @include('site.partials.nav')

    <main id="main">
        @unless($post->isLive())
            <p class="bg-flame px-4 py-2.5 text-center text-[0.9rem] font-medium text-on-flame">
                معاينة — المقال ده {{ $post->statusLabel() }} ولسه مش ظاهر للزوّار.
            </p>
        @endunless

        <article>
            <header class="grain relative overflow-clip border-b border-navy-2">
                <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[26rem] bg-[radial-gradient(closest-side,var(--flame),transparent_74%)] opacity-[calc(0.09*var(--bloom))]" aria-hidden="true"></div>

                <div class="container-zl py-[clamp(2.5rem,7vh,4.5rem)]">
                    <nav aria-label="مسار التصفّح" class="flex flex-wrap items-center gap-2 text-[0.8rem] text-cream-3">
                        <a href="{{ route('home') }}" class="inline-block py-1 transition-colors hover:text-flame-ink-hi">الرئيسية</a>
                        <span aria-hidden="true">/</span>
                        <a href="{{ route('blog.index') }}" class="inline-block py-1 transition-colors hover:text-flame-ink-hi">{{ $settings['blog_title'] }}</a>
                        @if($post->category)
                            <span aria-hidden="true">/</span>
                            <a href="{{ $post->category->url() }}" class="inline-block py-1 transition-colors hover:text-flame-ink-hi">{{ $post->category->name }}</a>
                        @endif
                    </nav>

                    <h1 class="mt-5 max-w-[22ch] text-[clamp(1.9rem,1.2rem+2.6vw,3.1rem)] leading-[1.2] font-bold tracking-[-0.02em] text-cream text-balance">{{ $post->title }}</h1>

                    @if($post->excerpt)
                        <p class="prose-zl mt-5 max-w-[62ch]">{{ $post->excerpt }}</p>
                    @endif

                    <div class="mt-7 flex flex-wrap items-center gap-x-4 gap-y-2 text-[0.82rem] text-cream-3">
                        @if($post->author)<span>{{ $post->author->name }}</span><span aria-hidden="true">·</span>@endif
                        <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
                        <span aria-hidden="true">·</span>
                        <span><span class="num">{{ $post->reading_minutes }}</span> دقيقة قراية</span>
                    </div>
                </div>
            </header>

            @if($post->cover_image)
                <div class="container-zl -mt-px">
                    <figure class="overflow-clip rounded-b-[1.5rem] border border-t-0 border-navy-2">
                        <img src="{{ $post->cover_image }}" alt="{{ $post->cover_alt ?: $post->title }}"
                             class="aspect-16/9 w-full object-cover" width="1280" height="720" fetchpriority="high">
                    </figure>
                </div>
            @endif

            <div class="container-zl grid gap-x-14 gap-y-10 py-[clamp(2.5rem,7vh,4rem)] lg:grid-cols-12">
                <div class="post-body min-w-0 lg:col-span-8">
                    {!! $toc['html'] !!}

                    @if($post->tags->isNotEmpty())
                        <div class="mt-14 flex max-w-none flex-wrap gap-2 border-t border-navy-2 pt-7">
                            @foreach($post->tags as $tag)
                                <a href="{{ $tag->url() }}" class="rounded-full border border-navy-2 px-3.5 py-1.5 text-[0.82rem] text-cream-2 no-underline transition-colors hover:border-navy-3 hover:text-cream">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Pinned rail: contents + share + the offer. Beside a long
                     article this column is otherwise pure whitespace. --}}
                <aside class="min-w-0 lg:col-span-4 lg:sticky lg:top-28 lg:self-start">
                    @if(count($toc['items']) > 1)
                        <nav aria-labelledby="toc-h" class="rounded-2xl border border-navy-2 bg-navy/40 p-5">
                            <h2 id="toc-h" class="text-[0.82rem] font-semibold text-cream-3">في المقال ده</h2>
                            <ol data-toc class="mt-3 flex flex-col gap-1">
                                @foreach($toc['items'] as $item)
                                    <li>
                                        <a href="#{{ $item['id'] }}"
                                           class="block border-e-2 border-transparent py-1.5 pe-3 text-[0.9rem] leading-[1.7] text-cream-2 transition-colors hover:text-cream aria-[current=true]:border-flame aria-[current=true]:text-cream">{{ $item['text'] }}</a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ rawurlencode($post->title.' — '.$post->url()) }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 rounded-full border border-navy-2 px-4 py-2.5 text-[0.85rem] text-cream-2 transition-colors hover:border-navy-3 hover:text-cream">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 1.67c2.2 0 4.27.86 5.82 2.42a8.19 8.19 0 0 1 2.42 5.82c0 4.54-3.7 8.24-8.25 8.24a8.25 8.25 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.26-8.24"/></svg>
                            شارك
                        </a>
                        <button type="button" data-copy-link="{{ $post->url() }}"
                                class="rounded-full border border-navy-2 px-4 py-2.5 text-[0.85rem] text-cream-2 transition-colors hover:border-navy-3 hover:text-cream">انسخ الرابط</button>
                    </div>

                    <div class="mt-6 rounded-2xl border border-flame/35 bg-navy/50 p-5">
                        <p class="text-[1.02rem] leading-[1.75] font-semibold text-cream">عايز نفس البطاطس دي في مطبخك؟</p>
                        <p class="mt-2 text-[0.92rem] leading-[1.9] text-cream-2">ابعتلنا نشاطك والكمية الشهرية وهنرجعلك بسعر مظبوط على حجمك.</p>
                        <a href="{{ route('home') }}#quote"
                           class="mt-4 block rounded-full bg-flame px-5 py-3 text-center text-[0.92rem] font-semibold text-on-flame transition-colors hover:bg-flame-hi">اطلب عرض سعر</a>
                    </div>
                </aside>
            </div>
        </article>

        @if($related->isNotEmpty())
            <section aria-labelledby="rel-h" class="border-t border-navy-2 py-[clamp(3rem,7vh,4.5rem)]">
                <div class="container-zl">
                    <h2 id="rel-h" class="text-h3 text-cream">اقرأ كمان</h2>
                    <ul class="mt-7 grid gap-x-8 gap-y-7 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($related as $r)
                            <li>
                                <a href="{{ $r->url() }}" class="group block">
                                    <div class="aspect-16/10 overflow-clip rounded-xl border border-navy-2 bg-navy-2">
                                        @if($r->cover_image)
                                            <img src="{{ $r->cover_image }}" alt="{{ $r->cover_alt ?: $r->title }}" loading="lazy"
                                                 class="size-full object-cover transition-transform duration-700 ease-out-quart group-hover:scale-[1.04]" width="400" height="250">
                                        @endif
                                    </div>
                                    <p class="mt-3 text-[0.75rem] text-cream-3">{{ $r->published_at?->translatedFormat('j F Y') }}</p>
                                    <h3 class="mt-1 text-[1.05rem] leading-[1.6] font-semibold text-cream transition-colors group-hover:text-flame-ink-hi">{{ $r->title }}</h3>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </main>

    @include('site.partials.footer')
@endsection
