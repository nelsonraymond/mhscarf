@props(['label' => 'Continue with Google'])

<a href="{{ route('auth.google') }}"
    {{ $attributes->merge(['class' => 'w-full inline-flex items-center justify-center gap-3 h-12 px-5 rounded-xl border border-line bg-white hover:bg-cream hover:border-pink-deep text-sm font-medium text-ink transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep']) }}>
    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47a5.54 5.54 0 0 1-2.4 3.63v3h3.88c2.27-2.09 3.54-5.17 3.54-8.87z"/>
        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.71-4.95H1.28v3.09A12 12 0 0 0 12 24z"/>
        <path fill="#FBBC05" d="M5.29 14.3A7.2 7.2 0 0 1 4.9 12c0-.8.14-1.57.39-2.3V6.61H1.28a12 12 0 0 0 0 10.78l4.01-3.09z"/>
        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.43-3.43C17.95 1.19 15.24 0 12 0A12 12 0 0 0 1.28 6.61l4.01 3.09C6.23 6.86 8.88 4.75 12 4.75z"/>
    </svg>
    {{ $label }}
</a>