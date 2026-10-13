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

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFF9F5;
            color: #3A3033;
        }
        .font-serif-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="min-h-screen antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden bg-[#FFF9F5]">
    <!-- Subtle Botanical / Floral Accents in Background -->
    <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#F8C8DC]/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#EFA7C1]/20 blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center mb-6">
        <a href="{{ route('home') }}" class="inline-block group">
            <div class="inline-flex items-center justify-center space-x-2">
                <span class="text-[#D98FAF] text-lg">❀</span>
                <span class="font-serif-display text-2xl font-semibold tracking-wider text-[#3A3033] uppercase">MH Scarf</span>
                <span class="text-[#D98FAF] text-lg">❀</span>
            </div>
            <p class="text-xs text-[#75686D] tracking-widest uppercase mt-0.5">Elegance in Every Wrap</p>
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
        <div class="bg-white py-8 px-6 sm:px-10 shadow-sm rounded-2xl border border-[#EBDDE2]">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
