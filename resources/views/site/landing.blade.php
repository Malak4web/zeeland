@extends('layouts.site')

@push('head')
    <link rel="preload" as="image" href="{{ asset('img/fries-dark.jpg') }}" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('img/fries-heap.jpg') }}" fetchpriority="high">
@endpush

@section('body')
    {{-- The Vue app owns everything below; Blade owns the <head> so the meta,
         canonical and JSON-LD are in the HTML Google fetches, not painted in
         after hydration. --}}
    <div id="app" data-latest="{{ $latest->isNotEmpty() ? $latest->map(fn ($p) => [
        'title' => $p->title,
        'url' => $p->url(),
        'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) $p->excerpt), 110),
        'image' => $p->cover_image,
        'minutes' => $p->reading_minutes,
        'date' => $p->published_at?->translatedFormat('j F Y'),
    ])->toJson(JSON_UNESCAPED_UNICODE) : '' }}"></div>
@endsection
