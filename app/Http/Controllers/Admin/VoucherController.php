<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Voucher::query()->withCount('usages')->latest();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%'.strtoupper(trim($request->input('search'))).'%');
        }

        match ($request->input('status')) {
            'active' => $query->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            'inactive' => $query->where('is_active', false),
            'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()),
            default => null,
        };

        return view('admin.vouchers.index', [
            'vouchers' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vouchers.form', [
            'voucher' => new Voucher(['type' => 'percentage', 'is_active' => true]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Voucher::create($this->validated($request));

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', ['voucher' => $voucher, 'isEdit' => true]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($this->validated($request, $voucher));

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function toggle(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['is_active' => ! $voucher->is_active]);

        return back()->with('success', $voucher->is_active ? 'Voucher diaktifkan.' : 'Voucher dinonaktifkan.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        // Voucher yang pernah dipakai harus tetap ada sebagai riwayat; nonaktifkan saja
        if ($voucher->usages()->exists()) {
            return back()->with('error', 'Voucher ini sudah pernah dipakai pada pesanan, jadi tidak bisa dihapus. Nonaktifkan saja.');
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $type = $request->input('type');

        $valueRules = ['required', 'numeric'];
        if ($type === 'percentage') {
            array_push($valueRules, 'gt:0', 'max:100');
        } elseif ($type === 'fixed') {
            array_push($valueRules, 'gt:0', 'max:99999999');
        } else {
            array_push($valueRules, 'min:0', 'max:99999999');
        }

        $endRules = ['nullable', 'date'];
        if ($request->filled('starts_at')) {
            $endRules[] = 'after:starts_at';
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('vouchers', 'code')->ignore($voucher?->id)],
            'type' => ['required', Rule::in(['percentage', 'fixed', 'free_shipping'])],
            'value' => $valueRules,
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            // Kuota baru tidak boleh lebih kecil dari yang sudah terpakai
            'usage_limit' => ['nullable', 'integer', 'min:'.max(1, (int) ($voucher?->used_count ?? 0))],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => $endRules,
        ], [
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'code.unique' => 'Kode voucher ini sudah dipakai.',
            'expires_at.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'usage_limit.min' => 'Kuota tidak boleh lebih kecil dari jumlah yang sudah terpakai.',
        ], [
            'code' => 'kode voucher',
            'value' => 'nilai',
            'min_order_amount' => 'minimal belanja',
            'max_discount_amount' => 'maksimal diskon',
            'usage_limit' => 'kuota total',
            'usage_limit_per_user' => 'batas per pengguna',
            'starts_at' => 'tanggal mulai',
            'expires_at' => 'tanggal berakhir',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Batas maksimal diskon hanya relevan untuk tipe persentase
        if ($data['type'] !== 'percentage') {
            $data['max_discount_amount'] = null;
        }

        return $data;
    }
}