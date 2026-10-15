<x-layouts.app>
    @section('title', ($review ? 'Edit Ulasan' : 'Beri Ulasan') . ' — MH Scarf Store')

    <style>
        .star-input input:checked ~ label,
        .star-input label:hover,
        .star-input label:hover ~ label { color: var(--color-gold, #c9a227); }
        .star-input input:focus-visible + label { outline: 2px solid var(--color-pink-deep, #d98fb0); outline-offset: 2px; border-radius: 4px; }
    </style>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('orders.index') }}" class="hover:text-pink-deep transition">Pesanan Saya</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('orders.show', $orderItem->order) }}" class="hover:text-pink-deep transition">#{{ $orderItem->order->order_number }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">{{ $review ? 'Edit Ulasan' : 'Beri Ulasan' }}</li>
            </ol>
        </nav>

        <div class="mb-6">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Ulasan Produk</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">{{ $review ? 'Edit ulasan Anda' : 'Bagaimana produk Anda?' }}</h1>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ $review ? route('reviews.update', $review) : route('reviews.store', $orderItem) }}"
            class="bg-white rounded-2xl border border-line p-5 sm:p-7 shadow-card space-y-6">
            @csrf
            @if ($review) @method('PUT') @endif

            {{-- Produk --}}
            @php $img = $orderItem->product?->primaryImage(); @endphp
            <div class="flex items-center gap-3.5 pb-5 border-b border-line">
                <div class="w-14 h-14 rounded-xl bg-cream border border-line overflow-hidden shrink-0 flex items-center justify-center">
                    @if ($img)
                        <img src="{{ Storage::url($img->image_path) }}" alt="{{ $orderItem->product_name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-[10px] tracking-widest text-pink-deep uppercase" aria-hidden="true">MH</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-sm text-ink">{{ $orderItem->product_name }}</p>
                    <p class="text-xs text-muted">Varian: {{ $orderItem->variant_name }}</p>
                </div>
            </div>

            {{-- Bintang --}}
            <fieldset>
                <legend class="text-xs font-semibold uppercase tracking-wider text-muted mb-2">Penilaian Anda</legend>
                <div class="star-input flex flex-row-reverse justify-end w-fit text-4xl text-line">
                    @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="star-{{ $i }}" value="{{ $i }}" class="sr-only"
                            {{ (int) old('rating', $review?->rating) === $i ? 'checked' : '' }} required>
                        <label for="star-{{ $i }}" class="cursor-pointer px-0.5 transition-colors" title="{{ $i }} bintang">
                            <span aria-hidden="true">★</span><span class="sr-only">{{ $i }} bintang</span>
                        </label>
                    @endfor
                </div>
            </fieldset>

            {{-- Komentar --}}
            <div>
                <label for="comment" class="block text-xs font-semibold uppercase tracking-wider text-muted">
                    Ulasan <span class="normal-case font-normal tracking-normal">(opsional)</span>
                </label>
                <textarea name="comment" id="comment" rows="5" maxlength="1000"
                    placeholder="Ceritakan bahan, warna, dan kenyamanan produk ini..."
                    class="mt-1.5 w-full px-3.5 py-2.5 rounded-xl border border-line bg-cream/40 text-sm text-ink placeholder:text-muted/70 focus:outline-none focus:border-pink-deep focus-visible:ring-2 focus-visible:ring-pink-deep transition">{{ old('comment', $review?->comment) }}</textarea>
                <p class="text-[11px] text-muted mt-1.5">Maksimal 1000 karakter. Nama Anda ditampilkan sebagian saja (contoh: "Nelson R.").</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <a href="{{ route('orders.show', $orderItem->order) }}"
                    class="inline-flex items-center justify-center px-5 h-12 rounded-xl border border-line bg-white hover:bg-cream text-sm font-medium text-ink transition">Batal</a>
                <x-primary-button type="submit" class="w-full sm:w-auto sm:min-w-[180px]">
                    {{ $review ? 'Simpan Perubahan' : 'Kirim Ulasan' }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-layouts.app>