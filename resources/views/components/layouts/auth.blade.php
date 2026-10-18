<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MH Scarf') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-serif-display {
            font-family: 'Playfair Display', ui-serif, Georgia, serif;
        }
    </style>
</head>
<body class="min-h-screen bg-cream font-body text-ink antialiased">
    <div class="relative min-h-screen w-full overflow-hidden">
        {{-- Background accents --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-[#F8C8DC]/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-[#EFA7C1]/20 blur-3xl"></div>

        {{--
            Jangan tambahkan max-w-md, mx-auto, atau card wrapper di sini.
            Halaman child mengatur sendiri layout dan lebar kontennya.
        --}}
        <main class="relative z-10 min-h-screen w-full">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
