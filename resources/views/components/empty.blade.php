@props(['title', 'hint' => null, 'action' => null, 'href' => null])

{{-- Empty states teach the screen instead of saying "no data". --}}
<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-navy-3 px-6 py-12 text-center']) }}>
    <p class="text-base font-medium text-cream">{{ $title }}</p>
    @if($hint)
        <p class="mx-auto mt-2 max-w-[46ch] text-sm leading-[1.9] text-cream-3">{{ $hint }}</p>
    @endif
    @if($action && $href)
        <a href="{{ $href }}" class="btn btn-primary mt-5">{{ $action }}</a>
    @endif
    {{ $slot }}
</div>
