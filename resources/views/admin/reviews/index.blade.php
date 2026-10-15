<x-layouts.admin>
    <x-slot:title>Ulasan - MH Scarf Admin</x-slot:title>
    <x-slot:header>Ulasan</x-slot:header>

    <x-admin.page-header
        title="Ulasan Pelanggan"
        description="Sembunyikan ulasan yang tidak sesuai. Rata-rata bintang produk diperbarui otomatis oleh sistem."
    />

    <div class="admin-card mb-6 p-4">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="relative sm:col-span-2 admin-search-wrap">
                <label for="review-search" class="sr-only">Cari ulasan</label>
                <input id="review-search" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari produk, pelanggan, atau isi ulasan..."
                    class="admin-input pl-10">
                <svg class="admin-search-icon pointer-events-none absolute left-3.5 top-[calc(50%+2px)] -translate-y-1/2 h-4 w-4 text-admin-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div>
                <label for="review-rating" class="sr-only">Rating</label>
                <select id="review-rating" name="rating" class="admin-input">
                    <option value="">Semua bintang</option>
                    @foreach ([5, 4, 3, 2, 1] as $r)
                        <option value="{{ $r }}" {{ (int) request('rating') === $r ? 'selected' : '' }}>{{ $r }} bintang</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <label for="review-visibility" class="sr-only">Status tampilan</label>
                <select id="review-visibility" name="visibility" class="admin-input">
                    <option value="">Semua status</option>
                    <option value="published" {{ request('visibility') === 'published' ? 'selected' : '' }}>Ditampilkan</option>
                    <option value="hidden" {{ request('visibility') === 'hidden' ? 'selected' : '' }}>Disembunyikan</option>
                </select>
                <button type="submit" class="admin-btn admin-btn-dark shrink-0">Filter</button>
                @if (request()->hasAny(['search', 'rating', 'visibility']))
                    <a href="{{ route('admin.reviews.index') }}" class="admin-btn admin-btn-secondary shrink-0">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($reviews->isEmpty())
            <x-admin.empty-state
                title="Belum ada ulasan"
                description="Ulasan pelanggan akan muncul di sini setelah pesanan selesai dinilai."
            />
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Pelanggan</th>
                            <th>Rating</th>
                            <th>Ulasan</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reviews as $review)
                            <tr class="align-top">
                                <td class="font-medium text-admin-text">{{ $review->product?->name ?? '-' }}</td>
                                <td>
                                    <p class="text-admin-text">{{ $review->user?->name ?? 'Pelanggan dihapus' }}</p>
                                    <p class="text-xs text-admin-muted">{{ $review->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="whitespace-nowrap"><x-star-rating :value="$review->rating" class="text-sm" /></td>
                                <td class="max-w-sm text-xs text-admin-muted">
                                    {{ $review->comment ? \Illuminate\Support\Str::limit($review->comment, 160) : '(tanpa komentar)' }}
                                </td>
                                <td>
                                    @if ($review->is_published)
                                        <span class="admin-badge border-emerald-200 bg-emerald-50 text-emerald-800">Ditampilkan</span>
                                    @else
                                        <span class="admin-badge border-slate-200 bg-slate-50 text-slate-600">Disembunyikan</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn admin-btn-secondary px-3 py-1.5 text-xs">
                                            {{ $review->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($reviews->hasPages())
                <div class="border-t border-admin-border p-4">{{ $reviews->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.admin>
