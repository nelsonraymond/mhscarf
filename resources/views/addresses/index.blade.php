<x-layouts.app>
    @section('title', 'Daftar Alamat Pengiriman - MH Scarf')

<div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <!-- Breadcrumb / Header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center space-x-2 text-xs text-muted mb-3">
                <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
                <span>/</span>
                <a href="{{ route('profile') }}" class="hover:underline">Akun</a>
                <span>/</span>
                <span class="text-ink font-medium">Buku Alamat</span>
            </div>
            <p class="text-[11px] tracking-[0.25em] uppercase text-pink-deep mb-2">Buku Alamat</p>
            <h1 class="font-display text-2xl sm:text-4xl font-bold text-ink">Alamat Pengiriman</h1>
            <p class="text-xs sm:text-sm text-muted mt-1">Kelola daftar alamat untuk kemudahan proses checkout pesanan Anda.</p>
        </div>
        <div>
            <a href="{{ route('addresses.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium rounded-xl shadow-card transition">
                <span class="mr-1.5 text-base">+</span> Tambah Alamat Baru
            </a>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-emerald-500">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($addresses->isEmpty())
        <div class="bg-white rounded-2xl border border-line p-10 text-center shadow-card">
            <div class="w-16 h-16 mx-auto rounded-full bg-cream border border-line flex items-center justify-center text-2xl text-pink-deep mb-4">
                📍
            </div>
            <h2 class="font-display text-xl font-bold text-ink mb-2">Belum Ada Alamat Tersimpan</h2>
            <p class="text-sm text-muted max-w-md mx-auto mb-6">
                Tambahkan alamat pengiriman utama Anda untuk mempercepat proses pemesanan hijab favorit Anda di MH Scarf Store.
            </p>
            <a href="{{ route('addresses.create') }}"
                class="inline-flex items-center px-5 py-2.5 bg-pink-deep hover:bg-pink-mauve text-white text-sm font-medium rounded-xl transition">
                + Tambah Alamat Sekarang
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($addresses as $address)
                <div class="bg-white rounded-2xl border {{ $address->is_default ? 'border-pink-deep shadow-sm ring-1 ring-pink-deep/30' : 'border-line' }} p-6 flex flex-col justify-between transition hover:border-pink-deep/70">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                <span class="font-semibold text-ink text-base">{{ $address->recipient_name }}</span>
                                @if($address->label)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-cream border border-line text-muted">
                                        {{ $address->label }}
                                    </span>
                                @endif
                            </div>
                            @if($address->is_default)
                                <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-pink-deep/15 text-pink-deep border border-pink-deep/30">
                                    Utama
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-muted font-mono mb-2">{{ $address->phone }}</p>

                        <p class="text-sm text-ink leading-relaxed mb-3">
                            {{ $address->fullAddress() }}
                        </p>

                        @if($address->notes)
                            <div class="p-2.5 rounded-lg bg-cream border border-line/70 text-xs text-muted italic mb-4">
                                Catatan: {{ $address->notes }}
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-line flex items-center justify-between text-xs mt-2">
                        <div>
                            @if(! $address->is_default)
                                <form method="POST" action="{{ route('addresses.default', $address) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-pink-deep hover:text-pink-mauve font-medium underline underline-offset-2">
                                        Jadikan Utama
                                    </button>
                                </form>
                            @else
                                <span class="text-emerald-600 font-medium flex items-center">
                                    <span class="mr-1">✓</span> Alamat Pengiriman Utama
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('addresses.edit', $address) }}" class="text-muted hover:text-ink font-medium">
                                Ubah
                            </a>

                            <form method="POST" action="{{ route('addresses.destroy', $address) }}"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.app>