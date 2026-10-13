@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'href' => null,
    'linkLabel' => null,
    'align' => 'left',
])

@php $center = $align === 'center'; @endphp

<div {{ $attributes->class([
    'flex flex-col gap-3',
    'items-center text-center' => $center,
    'md:flex-row md:items-end md:justify-between md:gap-8' => ! $center,
]) }}>
    <div class="{{ $center ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">
        @if ($eyebrow)
            <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.25em] text-pink-mauve sm:text-[11px]">
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="font-display text-[1.65rem] font-semibold leading-tight tracking-tight text-ink sm:text-3xl lg:text-4xl">
            {{ $title }}
        </h2>

        @if ($subtitle)
            <p class="mt-2 text-[13px] leading-relaxed text-muted sm:text-sm md:text-base">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if ($href && $linkLabel)
        <a href="{{ $href }}"
           class="inline-flex shrink-0 items-center gap-1.5 self-start whitespace-nowrap border-b border-pink-mauve/40 pb-0.5 text-sm font-medium text-pink-mauve transition-colors hover:border-pink-mauve hover:text-[#A56684] md:self-auto">
            <span>{{ $linkLabel }}</span>
            <span aria-hidden="true">→</span>
        </a>
    @endif
</div>