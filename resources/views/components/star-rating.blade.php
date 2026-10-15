@props(['value' => 0])

<span {{ $attributes->merge(['class' => 'inline-flex text-gold leading-none']) }} role="img"
    aria-label="Rating {{ number_format((float) $value, 1) }} dari 5">
    @for ($i = 1; $i <= 5; $i++)
        <span aria-hidden="true">{{ $i <= round($value) ? '★' : '☆' }}</span>
    @endfor
</span>