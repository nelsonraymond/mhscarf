<x-layouts.admin>
    <x-slot:title>Ulasan - MH Scarf Admin</x-slot:title>
    <x-slot:header>Moderasi Ulasan</x-slot:header>

    <div class="mb-6">
        <h1 class="font-serif-display text-2xl font-bold text-[#3A3033]">Ulasan Pelanggan</h1>
        <p class="text-xs text-[#75686D] mt-0.5">Sembunyikan ulasan yang melanggar. Rata-rata bintang produk diperbarui otomatis.</p>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-[#EBDDE2] mb-6 shadow-xs">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk, pelanggan, atau isi ulasan..."
                class="sm:col-span-2 w-full px-4 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-[#FFF9F5]/40">

            <select name="rating" onchange="this.form.submit()"
                class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                <option value="">Semua Bintang</option>
                @foreach ([5, 4, 3, 2, 1] as $r)
                    <option value="{{ $r }}" {{ (int) request('rating') === $r ? 'selected' : '' }}>{{ $r }} bintang</option>
                @endforeach
            </select>

            <select name="visibility" onchange="this.form.submit()"
                class="w-full px-3 py-2 rounded-xl border border-[#EBDDE2] text-sm text-[#3A3033] focus:outline-none focus:ring-2 focus:ring-[#D98FAF] bg-white">
                <option value="">Semua Status</option>
                <option value="published" {{ request('visibility') === 'published' ? 'selected' : '' }}>Ditampilkan</option>
                <option value="hidden" {{ request('visibility') === 'hidden' ? 'selected' : '' }}>Disembunyikan</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-[#EBDDE2] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#EBDDE2] text-sm">
                <thead class="bg-[#FFF9F5]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Produk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Pelanggan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Ulasan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#75686D]">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#75686D]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EBDDE2]">
                    @forelse ($reviews as $review)
                        <tr class="hover:bg-[#FFF9F5]/60 transition align-top">
                            <td class="px-4 py-3 text-[#3A3033] font-medium">{{ $review->product?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="text-[#3A3033]">{{ $review->user?->name ?? 'Pelanggan dihapus' }}</p>
                                <p class="text-xs text-[#75686D]">{{ $review->created_at->format('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap"><x-star-rating :value="$review->rating" class="text-sm" /></td>
                            <td class="px-4 py-3 text-xs text-[#75686D] max-w-sm">
                                {{ $review->comment ? \Illuminate\Support\Str::limit($review->comment, 160) : '(tanpa komentar)' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($review->is_published)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">Ditampilkan</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border bg-stone-50 text-stone-600 border-stone-200">Disembunyikan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-[#EBDDE2] text-[#3A3033] hover:bg-[#FFF9F5] transition cursor-pointer">
                                        {{ $review->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-[#75686D]">Belum ada ulasan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="p-4 border-t border-[#EBDDE2]">{{ $reviews->links() }}</div>
        @endif
    </div>
</x-layouts.admin>