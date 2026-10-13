@php
    $announcements = [
        'Gratis ongkir ke seluruh Indonesia, belanja mulai Rp75.000',
        'Koleksi Voal Pastel baru hadir',
        'Pengiriman setiap hari kerja, Senin sampai Sabtu',
        'Silk Satin Evening Edition sudah rilis',
        'Retur mudah dalam 7 hari setelah barang diterima',
    ];
@endphp

<style>
    .ann-bar {
        --ann-bg: #d98faf;
        --ann-fg: #ffffff;
        --ann-fade: 217, 143, 175; /* RGB dari --ann-bg */

        background: var(--ann-bg);
        color: var(--ann-fg);
        overflow: hidden;
        position: relative;
    }
    .ann-track { display: flex; width: max-content; animation: ann-scroll 40s linear infinite; }
    .ann-bar:hover .ann-track { animation-play-state: paused; }
    .ann-group { display: flex; flex-shrink: 0; align-items: center; }

    .ann-item {
        display: inline-flex; align-items: center; white-space: nowrap;
        padding: .6rem 0;
        font-size: 10.5px; font-weight: 600;
        letter-spacing: .2em; text-transform: uppercase;
    }
    .ann-item::after {
        content: "|"; margin: 0 1.25rem; font-weight: 400; opacity: .8;
    }

    .ann-bar::before, .ann-bar::after {
        content: ""; position: absolute; top: 0; bottom: 0; width: 2rem; z-index: 2; pointer-events: none;
    }
    .ann-bar::before { left: 0; background: linear-gradient(to right, rgb(var(--ann-fade)), rgba(var(--ann-fade), 0)); }
    .ann-bar::after { right: 0; background: linear-gradient(to left, rgb(var(--ann-fade)), rgba(var(--ann-fade), 0)); }

    @keyframes ann-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    @media (min-width: 640px) {
        .ann-item { font-size: 11px; letter-spacing: .22em; padding: .7rem 0; }
        .ann-item::after { margin: 0 1.75rem; }
        .ann-track { animation-duration: 55s; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ann-bar::before, .ann-bar::after { display: none; }
        .ann-track { animation: none; width: 100%; justify-content: center; }
        .ann-group:nth-child(2) { display: none; }
        .ann-item:not(:first-child) { display: none; }
        .ann-item::after { display: none; }
    }
</style>

<div class="ann-bar" role="region" aria-label="Pengumuman promo">
    <div class="ann-track">
        <div class="ann-group">
            @foreach ($announcements as $text)
                <span class="ann-item">{{ $text }}</span>
            @endforeach
        </div>
        <div class="ann-group" aria-hidden="true">
            @foreach ($announcements as $text)
                <span class="ann-item">{{ $text }}</span>
            @endforeach
        </div>
    </div>
</div>