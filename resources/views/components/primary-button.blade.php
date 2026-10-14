@props(['href' => null, 'type' => 'submit'])

{{-- PrimaryButton — deep pink, white text, radius 12px. Hover: white fill sweeps across (.btn-sweep in app.css). --}}
@php
    $classes = 'btn-sweep inline-flex items-center justify-center px-7 py-3 rounded-xl border border-pink-deep bg-pink-deep text-white text-sm font-medium hover:-translate-y-0.5 shadow-card hover:shadow-card-hover cursor-pointer min-h-[48px]';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif