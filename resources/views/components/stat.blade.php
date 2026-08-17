@props([
    'label',
    'value',
    'unit' => null,
    'hint' => null,
    'tone' => 'plain',   // plain | flame | good | warn | bad
    'delta' => null,     // signed percentage, already rounded
    'href' => null,
    'size' => 'md',      // md | lg
])

@php
    $tag = $href ? 'a' : 'div';
    $valueColor = match ($tone) {
        'flame' => 'text-flame-ink',
        'good'  => 'text-good',
        'warn'  => 'text-warn',
        'bad'   => 'text-bad',
        default => 'text-cream',
    };
    $valueSize = $size === 'lg'
        ? 'text-[clamp(1.7rem,1.2rem+1.6vw,2.35rem)]'
        : 'text-[clamp(1.35rem,1.1rem+0.9vw,1.75rem)]';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'panel flex flex-col justify-between gap-3 p-4 transition-colors duration-200'.($href ? ' hover:border-navy-3' : '')]) }}>

    <p class="text-2xs text-cream-3">{{ $label }}</p>

    <p class="flex items-baseline gap-1.5 leading-none">
        <span class="num {{ $valueSize }} {{ $valueColor }} font-semibold">{{ $value }}</span>
        @if($unit)<span class="text-xs text-cream-3">{{ $unit }}</span>@endif
    </p>

    @if($delta !== null || $hint)
        <p class="flex flex-wrap items-center gap-2 text-2xs text-cream-3">
            @if($delta !== null)
                {{-- Direction is carried by the arrow and the word, not the colour alone. --}}
                <span @class([
                    'inline-flex items-center gap-1',
                    'text-good' => $delta > 0,
                    'text-bad'  => $delta < 0,
                ])>
                    <span aria-hidden="true">{{ $delta > 0 ? '↑' : ($delta < 0 ? '↓' : '→') }}</span>
                    <span class="num">{{ abs($delta) }}%</span>
                    <span>{{ $delta > 0 ? 'زيادة' : ($delta < 0 ? 'نقص' : 'ثابت') }}</span>
                </span>
            @endif
            @if($hint)<span>{{ $hint }}</span>@endif
        </p>
    @endif
</{{ $tag }}>
