@props([
    'title',
    'description' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'px-6 py-14 text-center']) }}>
    <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-admin-bg text-admin-muted" aria-hidden="true">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M3 12h18M3 17h18"/>
        </svg>
    </div>
    <p class="text-sm font-medium text-admin-text">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-admin-muted">{{ $description }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="admin-btn admin-btn-primary mt-4">{{ $actionLabel }}</a>
    @endif
</div>
