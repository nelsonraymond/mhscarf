# Audit Refactoring MH Scarf Store (Fase 0)

> **Dokumen:** `docs/refactor-audit.md`  
> **Status:** Menunggu Persetujuan Pengguna  
> **Branch Kerja:** `refactor/fase-0-audit`  
> **Lingkungan:** Laravel 11 / PHP 8.3 / SQLite (Testing) & MySQL (Dev) / Tailwind CSS v4  
> **Tanggal Audit:** 17 Oktober 2026  

---

## 1. Baseline Pengujian & Code Style

Sebelum memulai refactoring, telah dijalankan pengujian otomatis dan pengecekan gaya kode untuk menetapkan baseline awal sistem.

### 1.1. Hasil `php artisan test`

```text
Tests:    188 total (179 passed, 7 failed, 2 errors, 4 risky)
Duration: 22.86s
```

#### Analisis Diagnostik Kegagalan Baseline:
Pengujian saat ini **belum 100% hijau** (179 lolos, 9 bermasalah). Berikut adalah akar masalah spesifik dari 9 kasus tersebut:

1. **Konfigurasi Lingkungan (`.env` vs `phpunit.xml` — berdampak pada 6 kegagalan):**
   - Di file `.env`: `MIDTRANS_EXPIRY_MINUTES=1440` (24 jam).
   - Di `phpunit.xml`, variabel `MIDTRANS_EXPIRY_MINUTES` **tidak dioverride**, sehingga saat test berjalan, waktu kedaluwarsa pesanan/reservasi menjadi 1440 menit alih-alih 15 menit.
   - Akibatnya:
     - `Tests\Feature\StockReservationTest::test_reservation_expires_at_is_set_correctly` gagal: test mengharapkan reservasi habis dalam 15 menit (`Carbon::now()->addMinutes(15)`), sedangkan sistem menghasilkan 1440 menit.
     - `Tests\Feature\PaymentTest::test_payment_page_is_blocked_after_the_payment_deadline` gagal: test memajukan waktu 20 menit (`travel(20)->minutes()`), tetapi pesanan baru expired setelah 1440 menit.
     - `Tests\Feature\ReleaseExpiredOrdersTest` (3 failed, 2 error): test memajukan waktu 25 menit, tetapi pesanan belum expired karena tenggat 1440 menit, sehingga sweeper melewati order dan mock gateway `fetchStatus` tidak terpanggil.
   - *Solusi untuk fase berikutnya:* Menambahkan `<env name="MIDTRANS_EXPIRY_MINUTES" value="15"/>` ke dalam `phpunit.xml` (atau mengaturnya via config runtime di base TestCase) tanpa mengubah `.env`.

2. **Perubahan Tampilan yang Belum Dicommit (berdampak pada 2 kegagalan):**
   - `Tests\Feature\PublicCatalogTest::test_public_product_detail_page_loads_with_variants_and_stock`: gagal karena teks di view `resources/views/catalog/show.blade.php` baris 263 diubah menjadi `"Stok tersedia"` (huruf kecil), sementara assertion test mengharapkan `"Stok Tersedia"` (Title Case).
   - `Tests\Feature\WishlistTest::test_authenticated_customer_sees_wishlist_badge_counter_in_navbar`: gagal karena badge counter wishlist `>2<` dihilangkan pada draft navbar baru (`resources/views/components/navbar.blade.php`).

---

### 1.2. Hasil `vendor/bin/pint --test`

Sebanyak **53 file PHP** terdeteksi belum memenuhi standar PSR-12 / Laravel Pint. File-file tersebut meliputi:
- `app/Console/Commands/ReleaseExpiredOrders.php`, `SendReviewReminders.php`
- `app/Contracts/PaymentGatewayInterface.php`
- `app/Http/Controllers/` (11 controller storefront & admin)
- `app/Models/` (Order, Review, StockReservation, Voucher, VoucherUsage)
- `app/Services/` (8 service class)
- `routes/web.php`, `routes/console.php`
- `tests/Feature/` (8 file feature test)

---

## 2. Peta Struktur Saat Ini

### 2.1. Controller

| Layer | Controller | File Path | Tanggung Jawab Utama |
|---|---|---|---|
| **Storefront** | `HomeController` | `app/Http/Controllers/HomeController.php` | Menampilkan beranda, kategori, best sellers, new arrivals, material spotlight, voucher aktif, dan review |
| | `CatalogController` | `app/Http/Controllers/CatalogController.php` | Menampilkan katalog produk, filter kategori/keyword/sale, detail produk, ulasan, & rekomendasi |
| | `CartController` | `app/Http/Controllers/CartController.php` | Manajemen keranjang belanja (tambah, perbarui qty, hapus, kosongkan) & aksi "Beli Sekarang" |
| | `CheckoutController` | `app/Http/Controllers/CheckoutController.php` | Alur checkout, validasi server-side, idempotency token, pembuatan pesanan, & integrasi voucher |
| | `PaymentController` | `app/Http/Controllers/PaymentController.php` | Tampilan pembayaran Snap Midtrans, endpoint status polling AJAX, & webhook receiver |
| | `OrderController` | `app/Http/Controllers/OrderController.php` | Riwayat pesanan pelanggan, rincian pesanan & pelacakan status, serta pembatalan mandiri |
| | `AddressController` | `app/Http/Controllers/AddressController.php` | CRUD buku alamat pelanggan & pengelolaan alamat default |
| | `WishlistController` | `app/Http/Controllers/WishlistController.php` | Daftar keinginan pelanggan (tambah, toggle, hapus) |
| | `ReviewController` | `app/Http/Controllers/ReviewController.php` | Pembuatan dan pengeditan ulasan pelanggan untuk item pesanan yang sudah diterima |
| | `CustomerAuthController` | `app/Http/Controllers/CustomerAuthController.php` | Registrasi, autentikasi login pelanggan, integrasi reCAPTCHA, & penggabungan keranjang tamu |
| | `GoogleAuthController` | `app/Http/Controllers/GoogleAuthController.php` | Alur OAuth Google via Socialite & auto-merge keranjang |
| | `PasswordResetController` | `app/Http/Controllers/PasswordResetController.php` | Permintaan tautan lupa password & reset password pelanggan |
| | `ProfileController` | `app/Http/Controllers/ProfileController.php` | Informasi ringkas akun & form update nama/email/password |
| | `JournalController` | `app/Http/Controllers/JournalController.php` | Menampilkan artikel gaya/edukasi dari konfigurasi `journal.php` |
| **Admin** | `AdminAuthController` | `app/Http/Controllers/AdminAuthController.php` | Login & logout khusus panel administrator |
| | `AdminDashboardController` | `app/Http/Controllers/AdminDashboardController.php` | Ringkasan metrik operasional, pesanan butuh tindakan, stok menipis, & refund tertunda |
| | `Admin\OrderController` | `app/Http/Controllers/Admin/OrderController.php` | Daftar pesanan admin, detail, transisi status, pembaruan nomor resi, pembatalan, & tanda refund |
| | `Admin\CategoryController` | `app/Http/Controllers/Admin/CategoryController.php` | CRUD kategori produk & hierarki induk/anak |
| | `Admin\ProductController` | `app/Http/Controllers/Admin/ProductController.php` | CRUD produk utama, penetapan harga dasar, dan status unggulan |
| | `Admin\ProductVariantController` | `app/Http/Controllers/Admin/ProductVariantController.php` | CRUD varian warna/ukuran/harga tambahan & penyesuaian stok manual |
| | `Admin\ProductImageController` | `app/Http/Controllers/Admin/ProductImageController.php` | Unggah gambar produk, pengaturan gambar utama, & penghapusan berkas fisik |
| | `Admin\ReviewController` | `app/Http/Controllers/Admin/ReviewController.php` | Moderasi ulasan (visibilitas publik/sembunyi) |
| | `Admin\VoucherController` | `app/Http/Controllers/Admin/VoucherController.php` | CRUD voucher promo, batas kuota total/per-user, tanggal berlaku, & status aktif |

---

### 2.2. Service Layer

| Service Class | File Path | Tanggung Jawab & Invarian Kunci |
|---|---|---|
| `InventoryService` | `app/Services/InventoryService.php` | Mutasi stok atomik (`lockForUpdate`), pencatatan riwayat `inventory_movements`, validasi `expectedStock` admin, pencegahan stok negatif pada tipe `out`. |
| `StockReservationService` | `app/Services/StockReservationService.php` | Penahanan stok berbatas waktu selama proses checkout, pelepasan reservasi kedaluwarsa (orphans & pesanan batal). |
| `CartService` | `app/Services/CartService.php` | Manajemen keranjang pengguna / tamu, kalkulasi subtotal, penggabungan keranjang saat login, otorisasi akses item keranjang. |
| `CheckoutService` | `app/Services/CheckoutService.php` | Orkestrasi transaksi checkout tunggal: penguncian baris keranjang dan varian berurutan (`orderBy('id')`), reservasi stok, voucher check & usage, pembuatan immutable order item snapshot, pembuatan shipment, dan pembersihan keranjang. |
| `PaymentService` | `app/Services/PaymentService.php` | Inisialisasi token Snap Midtrans dengan `Cache::lock`, verifikasi webhook & nominal tagihan, transisi status pembayaran satu arah, sweep pesanan kedaluwarsa via gateway fallback. |
| `MidtransPaymentGateway` | `app/Services/Payments/MidtransPaymentGateway.php` | Implementasi konkret `PaymentGatewayInterface` untuk integrasi API Midtrans Snap & verifikasi signature SHA512. |
| `OrderService` | `app/Services/OrderService.php` | State machine pesanan (`transitionStatus`), pembatalan atomik & idempoten (`cancelOrder`), pemulihan stok tunggal, pelepasan reservasi, pengembalian kuota voucher, penandaan `needs_refund`. |
| `ShippingService` | `app/Services/ShippingService.php` | Daftar tarif dan metode ekspedisi pengiriman (Regular, Express). |
| `VoucherService` | `app/Services/VoucherService.php` | Kalkulasi diskon (persentase, nominal tetap, gratis ongkir), evaluasi kelayakan voucher, penguncian baris voucher pada saat checkout (`lockAndEvaluate`), & pencatatan `voucher_usages`. |
| `ReviewService` | `app/Services/ReviewService.php` | Pembuatan & pembaruan ulasan berdasar `order_item_id` unik, penghitungan ulang rata-rata rating produk dengan locking row produk. |

---

### 2.3. Model & Relasi

| Model | Tabel | Soft Delete | Relasi Utama | Catatan Temuan |
|---|---|---|---|---|
| `User` | `users` | Tidak | `hasMany(Address, Order, Review, Cart, Wishlist, VoucherUsage)` | Method helper `isAdmin()`, `isCustomer()` |
| `Category` | `categories` | Ya | `belongsTo(parent)`, `hasMany(children, products)` | Scope `active()` |
| `Product` | `products` | Ya | `belongsTo(category)`, `hasMany(variants, images, reviews, wishlists, orderItems)` | Scope `active()`, `featured()`, `bestSeller()` |
| `ProductVariant` | `product_variants` | Tidak | `belongsTo(product)`, `hasMany(images, cartItems, orderItems, inventoryMovements)` | Scope `active()`. Belum ada scope `lowStock()` |
| `ProductImage` | `product_images` | Tidak | `belongsTo(product, variant)` | Flag `is_primary` |
| `InventoryMovement` | `inventory_movements` | Tidak | `belongsTo(variant, user)` | Polimorfik ke Order |
| `Cart` | `carts` | Tidak | `belongsTo(user)`, `hasMany(items)` | Mendukung session_id & user_id |
| `CartItem` | `cart_items` | Tidak | `belongsTo(cart, variant)` | Snapshot harga `unit_price` |
| `Order` | `orders` | Tidak | `belongsTo(user)`, `hasMany(items, payments, voucherUsages)`, `hasOne(shipment)` | Enum status & label badge. Syntax smell di `$fillable` |
| `OrderItem` | `order_items` | Tidak | `belongsTo(order, product, variant)`, `hasOne(review)` | Snapshot mutlak produk saat beli |
| `Payment` | `payments` | Tidak | `belongsTo(order)` | Field JSON `metadata` menyimpan snap_token & audit refund |
| `Shipment` | `shipments` | Tidak | `belongsTo(order)` | Kurir, tracking_number, status pengiriman |
| `StockReservation` | `stock_reservations` | Tidak | `belongsTo(variant, orderItem)` | Menahan kuantitas varian sebelum pesanan lunas |
| `Address` | `addresses` | Tidak | `belongsTo(user)` | Format alamat lengkap & flag default |
| `Wishlist` | `wishlists` | Tidak | `belongsTo(user, product)` | Composite unique `(user_id, product_id)` |
| `Review` | `reviews` | Ya | `belongsTo(product, user, orderItem)` | Unique per `order_item_id` |
| `Voucher` | `vouchers` | Tidak | `hasMany(usages)` | Tipe diskon & limit penggunaan |
| `VoucherUsage` | `voucher_usages` | Tidak | `belongsTo(voucher, user, order)` | Riwayat pemakaian per user |
| `Banner` | `banners` | Tidak | *Tidak ada relasi aktif* | **Model mati (tidak ada tabel migrasi/kontroler)** |
| `Notification` | `notifications` | Tidak | *Tidak ada relasi aktif* | **Model mati (tidak ada pemakaian)** |
| `ProductView` | `product_views` | Tidak | *Tidak ada relasi aktif* | **Model mati (tidak ada pemakaian)** |

---

### 2.4. Views & Komponen

- **Layouts (`resources/views/components/layouts/`):**
  - `app.blade.php`: Layout utama etalase storefront (termasuk announcement bar, navbar, footer, CSRF token).
  - `admin.blade.php`: Layout panel administrasi dengan sidebar responsif dan topbar.
  - `auth.blade.php`: Layout minimalis untuk halaman autentikasi (login, register, forgot-password).
- **Halaman Storefront:**
  - `home.blade.php`, `catalog/index.blade.php`, `catalog/show.blade.php`, `cart/index.blade.php`, `checkout/index.blade.php`, `payments/show.blade.php`, `orders/index.blade.php`, `orders/show.blade.php`, `addresses/index.blade.php`, `addresses/create.blade.php`, `addresses/edit.blade.php`, `wishlist/index.blade.php`, `profile.blade.php`, `profile/edit.blade.php`, `reviews/form.blade.php`, `journal/show.blade.php`, `faq.blade.php`.
- **Halaman Admin:**
  - `admin/dashboard.blade.php`, `admin/orders/index.blade.php`, `admin/orders/show.blade.php`, `admin/categories/index.blade.php`, `admin/categories/create.blade.php`, `admin/categories/edit.blade.php`, `admin/products/index.blade.php`, `admin/products/create.blade.php`, `admin/products/edit.blade.php`, `admin/vouchers/index.blade.php`, `admin/vouchers/form.blade.php`, `admin/reviews/index.blade.php`, `admin/auth/login.blade.php`.
- **Komponen Blade Reusable (`resources/views/components/`):**
  - `navbar.blade.php`, `footer.blade.php`, `announcement-bar.blade.php`, `product-card.blade.php`, `category-card.blade.php`, `price-display.blade.php`, `star-rating.blade.php`, `wishlist-button.blade.php`, `wishlist-badge-count.blade.php`, `cart-badge-count.blade.php`, `confirm-dialog.blade.php`, `promo-popup.blade.php`, `google-button.blade.php`, `recaptcha.blade.php`, `pagination.blade.php`, `section-heading.blade.php`, `primary-button.blade.php`, `secondary-button.blade.php`, `nav-link.blade.php`.

---

### 2.5. Peta Script JavaScript Inline

Terdapat **15 file view Blade** yang mengandung tag `<script>` JavaScript inline:

| File View Blade | Lokasi Baris | Fungsi Utama Script Inline |
|---|---|---|
| `resources/views/components/navbar.blade.php` | L229–L347 | Interaksi navbar: scroll hairline shadow, dropdown desktop, akordion mobile, search panel toggle, ESC key listener |
| `resources/views/catalog/show.blade.php` | L646–L652<br>L654–L835 | Schema.org JSON-LD structured data; kalkulasi harga varian dinamis, pemilihan warna/ukuran, kontrol qty, validasi "Beli Sekarang" |
| `resources/views/checkout/index.blade.php` | L310–L386<br>L405–L546 | Skrip Snap Midtrans; submit pembayaran AJAX, spinner submit, error extraction, pratinjau ongkir & voucher dinamis |
| `resources/views/payments/show.blade.php` | L166–L207 | Integrasi Snap SDK & callback handler (`onSuccess`, `onPending`, `onError`, `onClose`), polling status berkala |
| `resources/views/cart/index.blade.php` | L233–L256 | Sinkronisasi form update kuantitas otomatis saat nilai input berubah |
| `resources/views/catalog/index.blade.php` | L252–L268 | Autosubmit dropdown filter pengurutan katalog produk |
| `resources/views/home.blade.php` | L225–L238<br>L447–L456 | Carousel navigasi kategori & smooth scroll anchor handler |
| `resources/views/components/confirm-dialog.blade.php` | L37–L78 | Dialog konfirmasi modal kustom untuk tombol berspesifikasi `data-confirm` |
| `resources/views/components/promo-popup.blade.php` | L77–L114 | Logika kemunculan popup promo sekali per sesi pengguna via localStorage/sessionStorage |
| `resources/views/profile/edit.blade.php` | L129–L142 | Toggle visibilitas karakter sandi (show/hide password) |
| `resources/views/auth/login.blade.php` | L124–L133 | Toggle visibilitas sandi login |
| `resources/views/auth/register.blade.php` | L134–L143 | Toggle visibilitas sandi registrasi |
| `resources/views/auth/reset-password.blade.php` | L102–L111 | Toggle visibilitas konfirmasi sandi baru |
| `resources/views/admin/categories/create.blade.php` | L86–L117 | Auto-generate slug dari input nama kategori |
| `resources/views/admin/categories/edit.blade.php` | L98–L153 | Slug generator & preview gambar pengganti |
| `resources/views/admin/vouchers/form.blade.php` | L96–L120 | Penyesuaian dinamis label/satuan nilai input berdasarkan tipe voucher terpilih |
| `resources/views/components/layouts/admin.blade.php` | L164–L189 | Toggle navigasi sidebar mobile admin & dismiss alert |

---

## 3. Daftar Duplikasi, Kode Mati, dan Code Smell

Berikut adalah inventarisasi temuan teknis yang telah diverifikasi melalui penelusuran seluruh basis kode:

### 3.1. Kode Mati (Dead Code) yang Terbukti Tidak Digunakan

1. **Unreachable Return Statement di `CartController::buyNow`**
   - **Lokasi:** `app/Http/Controllers/CartController.php:143-147`
   - **Bukti:** Baris 143 melakukan `return redirect()->route('checkout.index')->with(...)`. Baris 146 mengulangi persis statement return yang sama sehingga baris 145–147 tidak akan pernah dieksekusi.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (1 menit)

2. **Unreachable Return Statement di `CatalogController::show`**
   - **Lokasi:** `app/Http/Controllers/CatalogController.php:151-157`
   - **Bukti:** Baris 151 mengembalikan view `catalog.show`. Tepat di bawahnya pada baris 156 terdapat `return view('catalog.index', ...)` yang tidak pernah tercapai.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (1 menit)

3. **Berkas View Lama `resources/views/welcome.blade.php` (72 KB)**
   - **Lokasi:** `resources/views/welcome.blade.php`
   - **Bukti:** Pencarian `grep -rin "welcome" routes app tests` menghasilkan 0 kecocokan. Route beranda mengarah ke `HomeController` yang memanggil `view('home')`.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (1 menit)

4. **Berkas Layout Usang `resources/views/layouts/admin.blade.php` & `resources/views/layouts/auth.blade.php`**
   - **Lokasi:** `resources/views/layouts/admin.blade.php` (122 baris), `resources/views/layouts/auth.blade.php` (45 baris)
   - **Bukti:** Seluruh view admin dan auth menggunakan Blade component syntax `<x-layouts.admin>` dan `<x-layouts.auth>` yang bersumber dari `resources/views/components/layouts/`. Tidak ada `@extends('layouts.admin')` atau `@extends('layouts.auth')` di manapun.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (2 menit)

5. **Model Scaffold Tanpa Fitur: `Banner`, `Notification`, `ProductView`**
   - **Lokasi:** `app/Models/Banner.php`, `app/Models/Notification.php`, `app/Models/ProductView.php` (beserta factories-nya di `database/factories/`)
   - **Bukti:** Tidak ada migrasi tabel, controller, route, view, maupun feature test yang memakainya.
   - **Tingkat Risiko:** Rendah (Perlu konfirmasi pemilik apakah disimpan untuk masa depan atau dibersihkan).
   - **Estimasi Usaha:** Kecil (5 menit)

---

### 3.2. Duplikasi Kode (Code Duplication)

1. **Aturan Validasi Alamat (Store vs Update)**
   - **Lokasi:** `app/Http/Controllers/AddressController.php:42-55` dan `app/Http/Controllers/AddressController.php:96-109`
   - **Bukti:** 12 baris array validasi alamat (recipient_name, phone, address_line, city, province, postal_code, dsb.) diduplikasi identik di method `store` dan `update`.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (ekstraksi ke `AddressRequest` atau `StoreAddressRequest`/`UpdateAddressRequest`).

2. **Aturan Validasi reCAPTCHA v2 (Login vs Register)**
   - **Lokasi:** `app/Http/Controllers/CustomerAuthController.php:39-44` dan `app/Http/Controllers/CustomerAuthController.php:93-98`
   - **Bukti:** Kondisi pemeriksaan environment testing/enabled dan inisiasi `new Recaptcha` diduplikasi di kedua method auth.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil.

3. **Query Stok Menipis (Low Stock Scope)**
   - **Lokasi:** `app/Http/Controllers/AdminDashboardController.php:32-37` dan `AdminDashboardController.php:77-80`
   - **Bukti:** Query closure `where(fn ($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_threshold')->orWhere('stock_qty', '<=', 0))` ditulis berulang dua kali.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (buat `scopeLowStock` pada `ProductVariant`).

4. **Query Voucher Aktif & Berlaku**
   - **Lokasi:** `app/Http/Controllers/HomeController.php:55-71` dan `app/Http/Controllers/Admin/VoucherController.php:23-28`
   - **Bukti:** Pengecekan `is_active`, `starts_at <= now()`, `expires_at >= now()`, dan `used_count < usage_limit` ditulis berulang dengan query builder mentah.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (buat `scopeAvailable()` / `scopeActive()` di model `Voucher`).

5. **Query Pesanan Butuh Refund**
   - **Lokasi:** `app/Http/Controllers/AdminDashboardController.php:90-95` dan `AdminDashboardController.php:97-99`
   - **Bukti:** `Order::whereHas('payments', fn ($q) => $q->where('metadata->needs_refund', true))` dijalankan dua kali terpisah berturut-turut (satu untuk `->get()`, satu untuk `->count()`).
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil.

6. **Logika Validasi Form Voucher di Controller**
   - **Lokasi:** `app/Http/Controllers/Admin/VoucherController.php:85-139`
   - **Bukti:** Logika validasi sepanjang 55 baris ditaruh sebagai protected method di controller daripada menggunakan Form Request terisolasi.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Sedang.

---

### 3.3. Code Smell & Pelanggaran Panduan Best Practice

1. **Kesalahan Definisi `$fillable` pada Model `Order`**
   - **Lokasi:** `app/Models/Order.php:39-40`
   - **Bukti:**
     ```php
     protected $fillable = [
         // ...
         'placed_at',
         'delivered_at' => 'datetime',
         'review_reminded_at' => 'datetime',
     ];
     ```
     Array `$fillable` seharusnya hanya berisi daftar nama string kolom. Sintaks asosiatif `'delivered_at' => 'datetime'` membuat atribut tersebut memiliki value `'datetime'`, sehingga pengecekan `in_array('delivered_at', $this->fillable)` gagal secara tidak terduga. Casting tipe data sudah didefinisikan dengan benar di method `casts()`.
   - **Tingkat Risiko:** Sedang (Memperbaiki mass-assignment keamanan order).
   - **Estimasi Usaha:** Kecil (1 menit).

2. **Self-Assignment Tanpa Makna pada `ProfileController`**
   - **Lokasi:** `app/Http/Controllers/ProfileController.php:52-54`
   - **Bukti:**
     ```php
     if (!empty($validated['password'])) {
         $validated['password'] = $validated['password']; // password mutator will hash
     }
     ```
     Penugasan nilai ke variabelnya sendiri tidak melakukan operasi apapun.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (1 menit).

3. **Bypass Relasi Eloquent untuk Agregat / Query Kepemilikan**
   - **Lokasi:**
     - `app/Http/Controllers/ProfileController.php:23-25`: `Order::where('user_id', $user->id)->count()`, `Address::where(...)`, `Wishlist::where(...)` alih-alih `$user->orders()->count()`, `$user->addresses()->count()`, `$user->wishlists()->count()`.
     - `app/Http/Controllers/WishlistController.php:18, 31, 54`: `Wishlist::where('user_id', $request->user()->id)` alih-alih `$request->user()->wishlists()->...`.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil.

4. **Pemeriksaan Otorisasi Manual (`abort(403)`) Menggantikan Laravel Policy**
   - **Lokasi:**
     - `AddressController.php:173-178` (`authorizeAddress`)
     - `OrderController.php:38-40` & `52-54`
     - `PaymentController.php:25-27` & `48`
     - `ReviewController.php:20, 37, 59, 72`
   - **Bukti:** Seluruh otorisasi kepemilikan data pengguna diperiksa ad-hoc via `if ($model->user_id !== Auth::id()) abort(403)`. Standar Laravel menyarankan pemanfaatan Policy (`AddressPolicy`, `OrderPolicy`, `ReviewPolicy`).
   - **Tingkat Risiko:** Sedang
   - **Estimasi Usaha:** Sedang.

5. **Resolusi Service via Helper Global `app()` di Controller**
   - **Lokasi:** `app/Http/Controllers/CustomerAuthController.php:59`
   - **Bukti:** `app(CartService::class)->mergeGuestCart(...)` dipanggil langsung di dalam method, melanggar kaidah dependency injection Laravel Boost Guidelines (lebih baik diinject via constructor/method).
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil.

6. **Leftover Debug Logging di Alur Produksi Checkout**
   - **Lokasi:** `app/Http/Controllers/CheckoutController.php:44-47`
   - **Bukti:**
     ```php
     \Log::info('Checkout quantity debug', [
         'buy_now' => $request->session()->get('buy_now'),
         'checkout_source' => $request->session()->get('checkout_source'),
     ]);
     ```
     Log debug ini selalu tertulis di log file setiap kali pengunjung membuka halaman checkout.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil (1 menit).

7. **Ketiadaan Form Request Classes di Sebagian Besar Controller**
   - **Lokasi:** Hampir seluruh controller (Address, CustomerAuth, AdminAuth, Checkout, Order, Review, ProductVariant, Voucher).
   - **Bukti:** Saat ini hanya ada 4 Form Request di seluruh sistem (`app/Http/Requests/Admin/` untuk Category dan Product). Logika validasi lainnya bercampur baur di dalam method controller.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Sedang.

8. **Inkonsistensi Pemanggilan Library Snap Midtrans di Frontend**
   - **Lokasi:** `resources/views/checkout/index.blade.php:310` vs `resources/views/payments/show.blade.php:166`
   - **Bukti:** `checkout/index.blade.php` melakukan hardcode URL ke `https://app.sandbox.midtrans.com/snap/snap.js`, sedangkan `payments/show.blade.php` secara dinamis memeriksa `$isProduction`.
   - **Tingkat Risiko:** Rendah
   - **Estimasi Usaha:** Kecil.

---

## 4. Usulan Urutan Fase Refactoring & Dependensi

Berdasarkan analisis risiko, ketergantungan komponen, dan invarian kritis, diusulkan 5 fase refactoring bertahap:

```text
┌────────────────────────────────────────────────────────┐
│  FASE 0: Audit & Baseline Lingkungan (Saat Ini)        │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│  FASE 1: Perbaikan Sintaks Model, Scope, & Form Request│
│  - Perbaiki bug $fillable pada Order.php               │
│  - Tambahkan scope reusable (lowStock, active, dsb.)   │
│  - Ekstraksi Form Request & pembersihan inline validasi│
│  - Inject dependensi (gantikan app() di Auth)          │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│  FASE 2: Deduplikasi & Otorisasi Controller            │
│  - Bersihkan dead code di CartController & Catalog     │
│  - Hapus leftover debug log pada CheckoutController    │
│  - Konsolidasi relasi Eloquent (Profile & Wishlist)    │
│  - Terapkan Policy authorization untuk model pengguna  │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│  FASE 3: Service Layer & Pemeliharaan Invarian Kritis  │
│  - Standarisasi return type & PHPDoc array shape       │
│  - Pastikan seluruh invarian (lockForUpdate,           │
│    idempotency token, sweeper, refund) terjaga 100%    │
│  - Konsolidasi helper pemformatan mata uang            │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│  FASE 4: Tampilan Blade, Komponen, & Dead View Cleanup │
│  - Hapus berkas mati (welcome.blade.php, layouts lama) │
│  - Konsolidasi script toggle password & Snap URL       │
│  - Pertahankan seluruh atribut DOM, ID JS, & teks ID   │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│  FASE 5: Formatting Codebase (Pint) & Final Baseline   │
│  - Jalankan `vendor/bin/pint --format agent`           │
│  - Verifikasi seluruh 188 feature test hijau           │
└────────────────────────────────────────────────────────┘
```

### Rincian Fase:

1. **Fase 1: Sintaks Model, Eloquent Scopes, dan Form Requests**
   - **Fokus:** Merapikan model dan lapisan validasi HTTP.
   - **Dependensi:** Tidak ada.
   - **Langkah Kerja:**
     - Perbaiki `$fillable` `Order.php`.
     - Buat `scopeLowStock()` pada `ProductVariant` dan `scopeAvailable()` pada `Voucher`.
     - Ekstraksi Form Request (`StoreAddressRequest`, `UpdateAddressRequest`, `CustomerLoginRequest`, `CustomerRegisterRequest`, `VoucherRequest`).
     - Refactor constructor injection `CartService` pada `CustomerAuthController`.

2. **Fase 2: Controller Deduplication, Policy, & Dead Code**
   - **Fokus:** Membersihkan controller dan merapikan otorisasi.
   - **Dependensi:** Fase 1.
   - **Langkah Kerja:**
     - Buang dead code return statement di `CartController::buyNow` dan `CatalogController::show`.
     - Buang leftover `\Log::info` di `CheckoutController`.
     - Gunakan relasi Eloquent `$user->orders()`, `$user->wishlists()`.
     - Terapkan `AddressPolicy`, `OrderPolicy`, `ReviewPolicy` untuk standarisasi otorisasi.

3. **Fase 3: Service Layer & Verifikasi Invarian Kritis**
   - **Fokus:** Memastikan lapisan domain tetap kokoh dan menjaga semua invarian transaksi/kunci.
   - **Dependensi:** Fase 2.
   - **Langkah Kerja:**
     - Verifikasi ulang: `InventoryService`, `CheckoutService::placeOrder`, `OrderService::cancelOrder`, `PaymentService`, `VoucherService`.
     - Tidak ada perubahan alur transaksi atau logika penguncian baris (`lockForUpdate`).
     - Tambahkan type hints eksplisit dan perbaiki PHPDoc array shapes.

4. **Fase 4: Perapian Blade View, Komponen, & Penghapusan File Usang**
   - **Fokus:** Mengurangi beban view dan menghapus template yang tidak dipakai.
   - **Dependensi:** Fase 3.
   - **Langkah Kerja:**
     - Hapus berkas usang: `resources/views/welcome.blade.php`, `resources/views/layouts/admin.blade.php`, `resources/views/layouts/auth.blade.php`.
     - Standarisasi pemanggilan URL Snap Midtrans di `checkout/index.blade.php` agar konsisten dengan `payments/show.blade.php`.
     - Pastikan semua ID dan data-attribute DOM JavaScript (`pd-add-btn`, `checkout-form`, `data-confirm`, dsb.) tidak tersentuh sama sekali.

5. **Fase 5: Standarisasi Gaya Kode (Laravel Pint) & Baseline Akhir**
   - **Fokus:** Memastikan gaya penulisan konsisten dan rapi di seluruh file yang diubah.
   - **Dependensi:** Fase 1–4.
   - **Langkah Kerja:**
     - Jalankan `vendor/bin/pint --format agent`.
     - Jalankan `php artisan test` dan pastikan seluruh test suite hijau tanpa regresi.

---

## 5. Ringkasan Status Akhir Fase 0

- [x] Peta struktur sistem (controller, service, model, view, komponen, JS inline) selesai disusun.
- [x] Inventarisasi kode mati, duplikasi, dan code smell lengkap dengan lokasi baris dan bukti.
- [x] Baseline pengujian `php artisan test` dan formatting `vendor/bin/pint --test` telah dicatat.
- [x] Akar penyebab kegagalan test pada baseline saat ini telah dianalisis secara akurat (konfigurasi `.env` & draft frontend uncommitted).
- [x] Tidak ada kode aplikasi atau pengujian yang diubah selama Fase 0.
- [x] Usulan fase refactoring telah dirumuskan secara sistematis.

**Menunggu persetujuan pengguna untuk melanjutkan ke Fase 1.**
