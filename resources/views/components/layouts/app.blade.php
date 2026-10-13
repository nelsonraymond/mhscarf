<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MH Scarf — Elegance in Every Wrap')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-cream text-ink font-body antialiased overflow-x-hidden">

    {{-- Global chrome — composed from reusable components (DESIGN.md §37) --}}
    <x-announcement-bar />
    <x-navbar />

    {{-- ============ FLASH MESSAGES (toast style per §34) ============ --}}
    @if (session('success') || session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" aria-live="polite">
            <div class="MH Scarf-rise bg-white border border-line rounded-xl shadow-card px-4 py-3 text-sm text-ink flex items-start gap-2">
                <span class="text-pink-deep font-semibold" aria-hidden="true">✓</span>
                <span>{{ session('success') ?? session('status') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" aria-live="polite">
            <div class="MH Scarf-rise bg-white border border-rose-200 rounded-xl shadow-card px-4 py-3 text-sm text-rose-700 flex items-start gap-2">
                <span class="font-semibold" aria-hidden="true">!</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- ============ PAGE CONTENT ============ --}}
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    {{-- ============ FOOTER (reusable component, DESIGN.md §26) ============ --}}
    <x-footer />
</body>
</html>
