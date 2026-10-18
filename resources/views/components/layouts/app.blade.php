<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MH Scarf — Exclusive Scarves')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Floating WhatsApp Button --}}
<a
    href="https://wa.me/6281234567890?text={{ urlencode('Halo MH Scarf, saya ingin bertanya tentang produk scarf.' ) }}"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Chat dengan MH Scarf melalui WhatsApp"
    class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition duration-300 hover:scale-110 hover:bg-[#20bd5a] focus:outline-none focus:ring-4 focus:ring-[#25D366]/40"
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="h-8 w-8"
        aria-hidden="true"
    >
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.611-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347Z"/>
        <path d="M20.52 3.449A11.815 11.815 0 0 0 12.084 0C5.495 0 .134 5.36.134 11.95c0 2.105.55 4.16 1.595 5.973L.063 24l6.218-1.631a11.892 11.892 0 0 0 5.797 1.475h.006c6.588 0 11.95-5.36 11.95-11.949a11.82 11.82 0 0 0-3.514-8.446ZM12.084 21.79h-.005a9.875 9.875 0 0 1-5.032-1.378l-.361-.214-3.69.968.985-3.598-.235-.369a9.86 9.86 0 0 1-1.51-5.25c.001-5.44 4.429-9.867 9.872-9.867a9.81 9.81 0 0 1 6.987 2.896 9.82 9.82 0 0 1 2.89 6.993c-.003 5.44-4.431 9.864-9.871 9.864Z"/>
    </svg>
</a>

<body class="min-h-screen flex flex-col bg-cream text-ink font-body antialiased overflow-x-hidden">

    {{-- Global chrome — composed from reusable components (DESIGN.md §37) --}}
    <x-announcement-bar />
    <x-navbar />

    {{-- ============ FLASH MESSAGES (toast style per §34) ============ --}}
    
{{-- Floating notification: tidak mendorong hero ke bawah --}}
@if (session('success') || session('status'))
    <div
        class="mh-floating-toast"
        role="status"
        aria-live="polite"
        id="mhSuccessToast"
    >
        <span class="flex-1">
            {{ session('success') ?? session('status') }}
        </span>
        <button
            type="button"
            class="mh-toast-close"
            onclick="this.parentElement.remove()"
            aria-label="Tutup notifikasi"
        >&times;</button>
    </div>
@endif

@if (session('error'))
    <div
        class="mh-floating-toast mh-floating-toast-error"
        role="alert"
        aria-live="assertive"
        id="mhErrorToast"
    >
        <span class="font-semibold" aria-hidden="true">!</span>
        <span class="flex-1">{{ session('error') }}</span>
        <button
            type="button"
            class="mh-toast-close"
            onclick="this.parentElement.remove()"
            aria-label="Tutup notifikasi"
        >&times;</button>
    </div>
@endif


    {{-- ============ PAGE CONTENT ============ --}}
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    {{-- ============ FOOTER (reusable component, DESIGN.md §26) ============ --}}
    <x-footer />

    {{-- Floating WhatsApp Button --}}
<a
    href="https://wa.me/6281234567890?text={{ urlencode('Halo MH Scarf, saya ingin bertanya tentang produk scarf.' ) }}"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="Chat WhatsApp MH Scarf"
    class="fixed bottom-6 right-6 z-[9999] flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl transition-transform duration-300 hover:scale-110"
>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="30"
        height="30"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path d="M21 11.5a8.38 8.38 0 0 1-9 8.5 8.5 8.5 0 0 1-4.1-1.05L3 20l1.1-4.6A8.5 8.5 0 1 1 21 11.5Z"></path>
        <path d="M8.5 8.5c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.7 1.7c.1.2.1.4-.1.6l-.5.6c.5 1 1.3 1.8 2.3 2.3l.6-.5c.2-.2.4-.2.6-.1l1.7.7c.3.1.4.3.4.5v.5c0 .3 0 .5-.4.7-.4.2-1.2.3-2.1-.1-1-.4-2-1-2.8-1.8-.8-.8-1.4-1.8-1.8-2.8-.4-.9-.3-1.7-.1-2.1Z"></path>
    </svg>
</a>

</body>
</html>
