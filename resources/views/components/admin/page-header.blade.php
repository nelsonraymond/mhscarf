@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-admin-text">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-admin-muted">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>
