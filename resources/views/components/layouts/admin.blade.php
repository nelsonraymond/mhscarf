<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Panel' }} - MH Scarf Store</title>

    @vite(['resources/css/app.css'])
</head>
<body class="admin-app min-h-screen antialiased">
    <a href="#admin-main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:shadow">
        Loncat ke konten
    </a>

    @php
        $navItems = [
            [
                'label' => 'Operasional',
                'links' => [
                    ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                    ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => 'Pesanan', 'icon' => 'orders'],
                    ['route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'label' => 'Ulasan', 'icon' => 'reviews'],
                    ['route' => 'admin.vouchers.index', 'pattern' => 'admin.vouchers.*', 'label' => 'Voucher', 'icon' => 'vouchers'],
                ],
            ],
            [
                'label' => 'Katalog',
                'links' => [
                    ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'products'],
                    ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'categories'],
                ],
            ],
        ];

        $icons = [
            'dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'orders' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'reviews' => 'M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.52.44c.5.04.7.67.32 1l-4.2 3.6a.56.56 0 00-.18.56l1.29 5.38a.56.56 0 01-.84.61l-4.73-2.89a.56.56 0 00-.58 0l-4.73 2.89a.56.56 0 01-.84-.61l1.29-5.38a.56.56 0 00-.18-.56l-4.2-3.6a.56.56 0 01.32-1l5.52-.44a.56.56 0 00.47-.35L11.48 3.5z',
            'products' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
            'categories' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            'vouchers' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
        ];
    @endphp

    <div class="flex min-h-screen">
        <div id="admin-backdrop" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" hidden></div>

        <aside id="admin-sidebar" class="flex flex-col bg-admin-sidebar text-white" aria-label="Navigasi admin">
            <div class="flex h-16 items-center justify-between gap-2 border-b border-white/10 px-4">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5 text-white">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-admin-accent text-xs font-bold">MH</span>
                    <span class="admin-brand-text truncate text-sm font-semibold tracking-wide">MH Scarf Admin</span>
                </a>
                <button type="button" id="admin-collapse" class="admin-collapse-btn rounded-md p-1.5 text-admin-sidebar-muted hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" aria-pressed="false" aria-label="Ciutkan sidebar">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                @foreach ($navItems as $group)
                    <p class="admin-nav-group-label mb-2 mt-4 px-2 text-[11px] font-semibold uppercase tracking-wider text-admin-sidebar-muted first:mt-0">
                        {{ $group['label'] }}
                    </p>
                    <ul class="space-y-0.5">
                        @foreach ($group['links'] as $link)
                            @php $active = request()->routeIs($link['pattern']); @endphp
                            <li>
                                <a href="{{ route($link['route']) }}"
                                    class="admin-nav-link flex items-center rounded-lg px-3.5 py-2.5 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white' : 'text-admin-sidebar-muted hover:bg-white/5 hover:text-white' }}"
                                    @if ($active) aria-current="page" @endif>
                                    <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons[$link['icon']] }}"/>
                                    </svg>
                                    <span class="admin-nav-label">{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach

                <div class="mt-6 border-t border-white/10 pt-4">
                    <a href="{{ route('shop.index') }}" target="_blank" rel="noopener noreferrer"
                        class="admin-nav-link flex items-center rounded-lg px-3.5 py-2.5 text-sm text-admin-sidebar-muted hover:bg-white/5 hover:text-white">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span class="admin-nav-label">Lihat toko</span>
                    </a>
                </div>
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="admin-user-meta min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[11px] text-admin-sidebar-muted">Administrator</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Keluar" aria-label="Keluar" class="rounded-md p-1.5 text-admin-sidebar-muted hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-admin-border bg-admin-surface px-4 sm:px-6">
                <button type="button" id="admin-menu" class="rounded-md p-2 text-admin-muted hover:bg-admin-bg hover:text-admin-text lg:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-admin-accent" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-admin-text">{{ $header ?? 'Manajemen Toko' }}</p>
                </div>
                <div class="hidden text-xs text-admin-muted sm:block">
                    {{ auth()->user()->email }}
                </div>
            </header>

            <main id="admin-main" class="mx-auto w-full max-w-[1400px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-medium">Terjadi kesalahan pada input:</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-confirm-dialog />

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var backdrop = document.getElementById('admin-backdrop');
            var menuBtn = document.getElementById('admin-menu');
            var collapseBtn = document.getElementById('admin-collapse');
            var storageKey = 'mh-admin-sidebar-collapsed';

            function isDesktop() {
                return window.matchMedia('(min-width: 1024px)').matches;
            }

            function setOpen(open) {
                sidebar.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                backdrop.hidden = !open;
                menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                menuBtn.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
                if (open) {
                    var first = sidebar.querySelector('a');
                    if (first) first.focus();
                } else {
                    menuBtn.focus();
                }
            }

            function setCollapsed(collapsed) {
                sidebar.classList.toggle('is-collapsed', collapsed);
                collapseBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                collapseBtn.setAttribute('aria-label', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
                try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (e) {}
            }

            if (menuBtn) {
                menuBtn.addEventListener('click', function () {
                    setOpen(!sidebar.classList.contains('is-open'));
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', function () { setOpen(false); });
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', function () {
                    setCollapsed(!sidebar.classList.contains('is-collapsed'));
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar.classList.contains('is-open') && !isDesktop()) {
                    setOpen(false);
                }
            });

            try {
                if (localStorage.getItem(storageKey) === '1') {
                    sidebar.classList.add('is-collapsed');
                    collapseBtn.setAttribute('aria-pressed', 'true');
                }
            } catch (e) {}
        })();
    </script>
</body>
</html>
