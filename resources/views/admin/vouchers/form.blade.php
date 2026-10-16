<x-layouts.admin>
    <x-slot:title>{{ $isEdit ? 'Ubah' : 'Buat' }} Voucher - MH Scarf Admin</x-slot:title>
    <x-slot:header>Manajemen Voucher</x-slot:header>

    @php
        $input = 'mt-1.5 w-full rounded-lg border border-admin-border bg-admin-surface px-3 py-2 text-sm text-admin-text focus:outline-none focus:ring-2 focus:ring-admin-accent';
        $label = 'block text-xs font-semibold uppercase tracking-wide text-admin-muted';
        $currentType = old('type', $voucher->type);
        $dt = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
    @endphp

    <div class="mb-6">
        <a href="{{ route('admin.vouchers.index') }}" class="text-xs text-admin-muted hover:text-admin-text">← Kembali ke daftar</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-admin-text">{{ $isEdit ? 'Ubah voucher '.$voucher->code : 'Buat voucher baru' }}</h1>
        @if ($isEdit)
            <p class="mt-1 text-sm text-admin-muted">Sudah dipakai {{ $voucher->used_count }}x. Pesanan lama menyimpan kode dan nominal diskonnya sendiri, jadi perubahan di sini tidak mengubah pesanan yang sudah dibuat.</p>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" class="admin-card max-w-3xl space-y-6 p-5 sm:p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="code" class="{{ $label }}">Kode voucher</label>
                <input type="text" name="code" id="code" value="{{ old('code', $voucher->code) }}" required maxlength="50"
                    placeholder="HEMAT10" autocomplete="off" style="text-transform: uppercase" class="{{ $input }} font-mono">
                <p class="mt-1 text-[11px] text-admin-muted">Huruf besar, angka, "-" dan "_". Tanpa spasi.</p>
            </div>

            <div>
                <label for="type" class="{{ $label }}">Jenis potongan</label>
                <select name="type" id="type" class="{{ $input }}">
                    <option value="percentage" {{ $currentType === 'percentage' ? 'selected' : '' }}>Persentase dari subtotal produk</option>
                    <option value="fixed" {{ $currentType === 'fixed' ? 'selected' : '' }}>Nominal tetap (Rp)</option>
                    <option value="free_shipping" {{ $currentType === 'free_shipping' ? 'selected' : '' }}>Gratis ongkir</option>
                </select>
            </div>

            <div>
                <label for="value" class="{{ $label }}" id="value-label">Nilai</label>
                <input type="number" step="0.01" min="0" name="value" id="value" value="{{ old('value', $voucher->value) }}" required class="{{ $input }}">
                <p class="mt-1 text-[11px] text-admin-muted" id="value-hint"></p>
            </div>

            <div id="field-max-discount">
                <label for="max_discount_amount" class="{{ $label }}">Maksimal diskon (Rp)</label>
                <input type="number" step="1" min="0" name="max_discount_amount" id="max_discount_amount"
                    value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}" placeholder="Kosongkan bila tanpa batas" class="{{ $input }}">
            </div>

            <div>
                <label for="min_order_amount" class="{{ $label }}">Minimal belanja (Rp)</label>
                <input type="number" step="1" min="0" name="min_order_amount" id="min_order_amount"
                    value="{{ old('min_order_amount', $voucher->min_order_amount) }}" placeholder="Kosongkan bila tanpa minimal" class="{{ $input }}">
            </div>

            <div>
                <label for="usage_limit" class="{{ $label }}">Kuota total</label>
                <input type="number" min="1" name="usage_limit" id="usage_limit"
                    value="{{ old('usage_limit', $voucher->usage_limit) }}" placeholder="Kosongkan bila tanpa batas" class="{{ $input }}">
            </div>

            <div>
                <label for="usage_limit_per_user" class="{{ $label }}">Batas per pengguna</label>
                <input type="number" min="1" name="usage_limit_per_user" id="usage_limit_per_user"
                    value="{{ old('usage_limit_per_user', $voucher->usage_limit_per_user) }}" placeholder="Kosongkan bila tanpa batas" class="{{ $input }}">
            </div>

            <div>
                <label for="starts_at" class="{{ $label }}">Berlaku mulai</label>
                <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $dt($voucher->starts_at)) }}" class="{{ $input }}">
            </div>

            <div>
                <label for="expires_at" class="{{ $label }}">Berlaku sampai</label>
                <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', $dt($voucher->expires_at)) }}" class="{{ $input }}">
            </div>
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-admin-text">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}
                class="h-4 w-4 rounded border-admin-border accent-[var(--color-admin-accent,#d98faf)]">
            Voucher aktif (bisa dipakai pembeli)
        </label>

        <div class="flex flex-col-reverse gap-3 border-t border-admin-border pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.vouchers.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-admin-border px-4 py-2 text-sm font-medium text-admin-text hover:bg-admin-bg">Batal</a>
            <button type="submit" class="admin-btn admin-btn-primary">{{ $isEdit ? 'Simpan perubahan' : 'Buat voucher' }}</button>
        </div>
    </form>

    <script>
        (function () {
            var type = document.getElementById('type');
            var maxField = document.getElementById('field-max-discount');
            var valueLabel = document.getElementById('value-label');
            var valueHint = document.getElementById('value-hint');

            function sync() {
                var t = type.value;
                maxField.classList.toggle('hidden', t !== 'percentage');
                if (t === 'percentage') {
                    valueLabel.textContent = 'Persentase (%)';
                    valueHint.textContent = 'Contoh: 10 untuk diskon 10% dari subtotal produk.';
                } else if (t === 'fixed') {
                    valueLabel.textContent = 'Potongan (Rp)';
                    valueHint.textContent = 'Contoh: 15000. Tidak pernah melebihi subtotal produk.';
                } else {
                    valueLabel.textContent = 'Batas potongan ongkir (Rp)';
                    valueHint.textContent = 'Isi 0 agar ongkir ditanggung penuh. Isi angka untuk membatasi, misalnya 15000.';
                }
            }

            type.addEventListener('change', sync);
            sync();
        })();
    </script>
</x-layouts.admin>