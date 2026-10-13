<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MH Scarf Store') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Inline fallback so typography still renders even if the built CSS fails to load --}}
    <style>
        .font-serif-display {
            font-family: 'Playfair Display', ui-serif, Georgia, serif;
        }
    </style>
</head>
<body class="min-h-screen antialiased flex flex-col justify-center py-10 sm:py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden bg-cream text-ink font-body">
    <!-- Subtle Botanical / Floral Accents in Background -->
    <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#F8C8DC]/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#EFA7C1]/20 blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center mb-6">
        <a href="{{ route('home') }}" class="inline-block" aria-label="MH Scarf — Beranda">
            <span class="font-display text-2xl font-bold tracking-[0.18em] text-ink uppercase">MH Scarf</span>
            <p class="text-[11px] text-muted tracking-[0.2em] uppercase mt-1">Elegance in Every Wrap</p>
        </a>
    </div>

    <div class="mx-auto w-full max-w-md relative z-10">
        <div class="bg-white py-8 px-5 sm:px-10 shadow-card rounded-2xl border border-line">
            {{ $slot }}
        </div>
        <p class="mt-6 text-center text-xs text-muted">
            <a href="{{ route('shop.index') }}" class="hover:text-pink-deep transition-colors underline underline-offset-2">← Kembali ke katalog</a>
        </p>
    </div>
</body>
</html>
