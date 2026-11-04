# Catatan Hasil Verifikasi & Audit Teknis SRS MH SCARF
**Dokumen Pendukung:** Revisi Final Software Requirements Specification (Bab 1 - Bab 4)  
**Proyek:** MH SCARF E-Commerce Platform  
**Program Studi:** Sistem Informasi, Fakultas Ilmu Komputer, Universitas Amikom Yogyakarta  
**Waktu Audit:** Oktober 2026 (Versi 2.1 Final Quality Patch)  
**Auditor:** Senior System Analyst, Software Requirement Engineer, UML Designer, Database Architect, & Academic Documentation Specialist  

---

## 1. Pendahuluan

Dokumen ini memuat catatan audit komprehensif hasil penelusuran terhadap basis kode sumber (*source code*), skema basis data (*database migrations*), berkas konfigurasi lingkungan, pengujian otomatis (*automated feature tests*), serta dokumen perancangan awal (`SRS.md`, `PRD Website E-Commerce Hijab.md`, dan `ERD-DATABASE.md`).

Tujuan pembuatan dokumen ini adalah memisahkan catatan temuan teknis, gap analysis, status pengujian, dan transparansi status fitur dari naskah akademik utama `SRS-MH-SCARF-BAB-1-4.md`.

---

## 2. Fitur yang Berhasil Diverifikasi Penuh (Implemented)

Berdasarkan penelusuran langsung terhadap rute (`routes/web.php` dan `routes/console.php`), lapisan pengendali (*controllers*), lapisan layanan (*service layer*), model Eloquent, dan *static test code review* pada repositori:

| No | ID Fitur | Modul / Fitur | Bukti Teknis Kode Sumber | Bukti Pengujian Terkait | Keterangan Status |
|---|---|---|---|---|---|
| 1 | `MHSC-001` | Penjelajahan Beranda & Katalog | `CatalogController@index`, rute `shop.index`, view `catalog/index` | `PublicCatalogTest.php` | Terverifikasi: Mendukung filter kategori, pencarian teks (`q`), diskon (`sale=1`), dan sorting (`newest`, `price_asc`, `price_desc`, `best_seller`). |
| 2 | `MHSC-002` | Pencarian & Filter Produk | `CatalogController@index` (query scoping) | `PublicCatalogTest.php` | Terverifikasi: Pencarian teks multi-kolom (nama, deskripsi, bahan). |
| 3 | `MHSC-003` | Penayangan Detail & Varian Hijab | `CatalogController@show`, rute `shop.product`, view `catalog/show` | `PublicCatalogTest.php` | Terverifikasi: Menampilkan varian multi-warna, hex code, stok riil, ulasan terverifikasi, dan produk sejenis. |
| 4 | `MHSC-004` | Pengelolaan Keranjang Tamu | `CartController`, `CartService`, model `Cart`, model `CartItem` | `CartTest.php` | Terverifikasi: Item belanja diikat pada `session_id` peramban tanpa harus login. |
| 5 | `MHSC-005` | Registrasi Akun Pelanggan Manual | `CustomerAuthController@register`, Form Request, model `User` | `CustomerAuthTest.php` | Terverifikasi: Registrasi pelanggan dengan hashing kata sandi Bcrypt dan role default `customer`. |
| 6 | `MHSC-006` | Login Akun Manual Pelanggan | `CustomerAuthController@store`, middleware `throttle:5,1` | `CustomerAuthTest.php` | Terverifikasi: Proteksi brute-force (maksimal 5 percobaan gagal per menit). |
| 7 | `MHSC-007` | Login Google OAuth (Socialite) | `GoogleAuthController`, `config/services.php` (`google`) | `CustomerAuthTest.php` | Terverifikasi: Login instan via Google. Memiliki validasi keamanan mencegah akun `role = 'admin'` masuk via OAuth. |
| 8 | `MHSC-008` | Permintaan Reset Kata Sandi | `PasswordResetController`, tabel `password_reset_tokens` | Code review terverifikasi | Terverifikasi: Pengiriman token reset via email pelanggan berbatas waktu. |
| 9 | `MHSC-029` | Penayangan Panduan & FAQ | Rute `Route::view('/faq', 'faq')`, view `resources/views/faq.blade.php` | Code review terverifikasi | Terverifikasi: Halaman informasi bantuan belanja dan kebijakan butik dapat diakses publik. |
| 10 | `MHSC-030` | Penayangan Artikel Jurnal Fashion | `JournalController@show`, rute `journal.show`, view `journal/show` | Code review terverifikasi | Terverifikasi: Edukasi karakteristik bahan hijab dan petunjuk perawatan busana. |
| 11 | `MHSC-009` | Penggabungan Keranjang Otomatis | `CartService::mergeGuestCart()` | `CartCheckoutIntegrationTest.php` | Terverifikasi: Item belanja sesi tamu otomatis digabungkan ke akun pelanggan saat login. |
| 12 | `MHSC-010` | Pembelian Langsung (*Buy Now*) | `CartController@buyNow`, `CheckoutService::placeOrder` | `CheckoutTest.php` | Terverifikasi: Melewati alur keranjang belanja dan langsung menuju formulir checkout. |
| 13 | `MHSC-011` | Pengelolaan Daftar Keinginan | `WishlistController`, model `Wishlist`, rute `wishlist.toggle` | `WishlistTest.php` | Terverifikasi: Toggle simpan/hapus varian hijab favorit pelanggan terautentikasi. |
| 14 | `MHSC-012` | Pengelolaan Buku Alamat | `AddressController`, model `Address` | `AddressTest.php` | Terverifikasi: CRUD buku alamat, penandaan alamat default, dan validasi data wilayah. |
| 15 | `MHSC-013` | Perhitungan Ongkir Real-time | `CheckoutShippingController`, `ShippingService`, `RajaOngkirClient` | `RajaOngkirShippingTest.php` | Terverifikasi: Integrasi API Komerce V2 dengan kalkulasi akumulasi berat paket varian. |
| 16 | `MHSC-014` | Penggunaan Voucher Promo | `VoucherService`, model `Voucher` | `VoucherCheckoutTest.php` | Terverifikasi: Tipe persentase, nominal tetap, dan gratis ongkir; penguncian kuota saat checkout. |
| 17 | `MHSC-015` | Checkout Idempotent & Reservasi | `CheckoutController@store`, `CheckoutService::placeOrder` | `CheckoutTest.php` | Terverifikasi: Idempotensi bersumber dari database (`orders.checkout_token`), baris dikunci `lockForUpdate()`, dan reservasi stok 15 menit. |
| 18 | `MHSC-016` | Pembayaran Midtrans Snap | `PaymentController@show`, `PaymentService::initializePayment()` | `PaymentTest.php` | Terverifikasi: Menghindari duplikasi request pada lingkungan serverless menggunakan sewa basis data (*durable lease* 60 detik). |
| 19 | `MHSC-017` | Riwayat & Pembatalan Pesanan | `OrderController@index`, `OrderController@cancel` | `CustomerOrderCancellationTest.php` | Terverifikasi: Pelanggan dapat membatalkan pesanan mandiri selama status masih `pending`. |
| 20 | `MHSC-018` | Pelacakan Pengiriman (Tracking) | `ShipmentTrackingService`, `RajaOngkirClient@trackWaybill` | `ShipmentTrackingTest.php` | Terverifikasi: Memanggil API Komerce dengan 3 parameter (AWB, kode kurir, 5 digit akhir no telepon) dan jeda cooldown cache 5 menit. |
| 21 | `MHSC-020` | Autentikasi Panel Administrator | `AdminAuthController`, middleware `admin` (`EnsureUserIsAdmin`) | `AdminAuthTest.php` | Terverifikasi: Akses panel `/admin` diproteksi verifikasi peran (*role verification*). |
| 22 | `MHSC-021` | Monitoring Dashboard Metrik | `AdminDashboardController@index`, view `admin/dashboard` | `AdminDashboardTest.php` | Terverifikasi: Ringkasan metrik omzet pembayaran sah, antrean pesanan butuh proses, peringatan stok menipis, dan review pending. |
| 23 | `MHSC-022` | Manajemen Kategori Hijab | `Admin\CategoryController`, disk `supabase` | `AdminCategoryTest.php` | Terverifikasi: CRUD kategori dan upload berkas media ke Supabase Storage. |
| 24 | `MHSC-023` | Manajemen Produk & Galeri Foto | `Admin\ProductController`, `Admin\ProductImageController` | `AdminProductTest.php` | Terverifikasi: CRUD produk, harga dasar, diskon coret, dan galeri foto Supabase Storage. |
| 25 | `MHSC-024` | Manajemen Varian & Stok Fisik | `Admin\ProductVariantController` | `AdminProductTest.php` | Terverifikasi: Pengelolaan varian warna hex, ukuran, kuantitas stok gudang, dan berat paket. |
| 26 | `MHSC-025` | Pemrosesan Status Pesanan | `OrderService::transitionStatus()`, rute `admin.orders.status` | `OrderStateTransitionTest.php` | Terverifikasi: Transisi terarah `pending` &rarr; `confirmed` &rarr; `processing` &rarr; `packed` &rarr; `shipped` &rarr; `delivered` &rarr; `completed`. |
| 27 | `MHSC-026` | Manajemen Pengiriman & Resi | `Admin\OrderController@updateShipment`, model `Shipment` | Code review terverifikasi | Terverifikasi: Validasi ketat status `shipped` hanya diizinkan bila pesanan lunas dan resi AWB telah diisi lengkap. |
| 28 | `MHSC-027` | Manajemen Voucher Promo | `Admin\VoucherController`, model `Voucher` | `AdminVoucherTest.php` | Terverifikasi: Pembuatan dan pengelolaan kuota voucher promo oleh administrator. |
| 29 | `MHSC-028` | Moderasi Ulasan Pelanggan | `Admin\ReviewController@toggle`, model `Review` | Code review terverifikasi | Terverifikasi: Admin dapat mengaktifkan atau menyembunyikan ulasan (`is_published`). |

---

## 3. Fitur yang Hanya Tersedia Sebagian (Partially Implemented)

Fitur berikut telah memiliki struktur tabel, model, dan logika backend, namun belum tersedia lengkap pada antarmuka pengguna:

1. **Unggah Foto pada Ulasan Pembeli (`photo_path`):**
   - *Status Database:* Kolom `photo_path` telah tersedia pada migrasi tabel `reviews`.
   - *Status Controller:* `ReviewController@validated` hanya memproses `rating` (1–5) dan teks `comment`.
   - *Kondisi Aktual:* Komponen formulir pengunggahan foto ulasan pada tampilan `resources/views/reviews/form.blade.php` belum diaktifkan.
2. **Antarmuka Audit Mutasi Inventaris Gudang (`inventory_movements`):**
   - *Status Backend:* Pencatatan mutasi fisik (tipe `in`, `out`, `adjustment`, `return`) berjalan konsisten di service layer (`InventoryService` dan `StockReservationService`).
   - *Kondisi Aktual:* Belum tersedia menu tampilan antarmuka khusus pada dashboard backoffice untuk membaca log mutasi fisik secara visual.

---

## 4. Fitur yang Belum Diimplementasikan / Berstatus Rencana (Planned)

Fitur-fitur berikut terdapat dalam skema migrasi awal atau dokumen konseptual, namun belum memiliki pengontrol fungsional aktif:

1. **Pengelolaan Banner Promosi Dinamis (`banners`):**
   - Skema tabel `banners` dan model `Banner` telah dibuat, namun tampilan beranda saat ini masih menggunakan banner statis dan belum tersedia controller admin untuk mengelola banner secara dinamis.
2. **Kotak Masuk Notifikasi In-App Pengguna (`notifications`):**
   - Skema tabel `notifications` dan model tersedia, namun belum terdapat antarmuka pusat notifikasi di storefront customer.
3. **Pencatatan Log Kunjungan Halaman Produk (`product_views`):**
   - Skema tabel tersedia, namun tidak dipanggil saat rute detail produk dibuka (`CatalogController@show`).
4. **Ekspor Laporan Keuangan (PDF / Excel):**
   - Dashboard admin menyajikan ringkasan metrik penjualan, namun sistem belum memiliki fitur ekspor rekapitulasi data ke format berkas spreadsheet atau PDF.

---

## 5. Audit dan Standardisasi 26 Kebutuhan Fungsional (FR)

Pada versi sebelumnya, dokumen menyebutkan 20 FR padahal rinciannya terdiri dari **26 FR**, serta terdapat kesenjangan di mana hanya 12 FR memiliki Precondition dan 4 FR memiliki Postcondition. Melalui Final Patch Versi 2.1, seluruh **26 Functional Requirements (FR)** telah diaudit dan diseragamkan dengan format lengkap 8 komponen:

| Kelompok Modul | Rentang Kode FR | Jumlah FR | Kelengkapan Komponen (8/8) | Catatan Verifikasi Kunci |
|---|---|---|---|---|
| **Autentikasi** | `FR-AUTH-001` s/d `FR-AUTH-005` | 5 FR | 100% Lengkap | Redaksi FR-AUTH-004 dikoreksi menjadi *"email tautan reset kata sandi terkirim"*. Proteksi brute-force login (`throttle:5,1`) dan penolakan admin via Google OAuth terverifikasi. |
| **Katalog & Info** | `FR-CAT-001`, `FR-PROD-001`, `FR-PROD-002`, `FR-INFO-001` | 4 FR | 100% Lengkap | Mencakup pencarian teks bebas multi-kolom, varian warna/ukuran, Buy Now, serta halaman publik FAQ dan Jurnal Fashion (`FR-INFO-001`). |
| **Keranjang & Wishlist** | `FR-CART-001`, `FR-CART-002`, `FR-WSH-001` | 3 FR | 100% Lengkap | Penggabungan keranjang tamu ke akun saat login (*guest cart merging*) dan toggle wishlist terverifikasi pada model & relasi. |
| **Alamat & Ongkir** | `FR-ADDR-001`, `FR-SHIP-001`, `FR-SHIP-002` | 3 FR | 100% Lengkap | Validasi buku alamat default, kalkulasi ongkir multi-kurir Komerce V2, dan pelacakan resi 3-parameter dengan cache 5 menit. |
| **Checkout & Order** | `FR-CHK-001` | 1 FR | 100% Lengkap | Idempotensi transaksi berpusat pada database MySQL (`orders.checkout_token`), baris dikunci `lockForUpdate()`, reservasi stok 15 menit. |
| **Pembayaran** | `FR-PAY-001` s/d `FR-PAY-003` | 3 FR | 100% Lengkap | Inisialisasi Snap token bersewa 60 detik, verifikasi webhook SHA-512 dengan toleransi Rp1.0, serta pelepasan pesanan kedaluwarsa via command scheduler. |
| **Ulasan** | `FR-REV-001`, `FR-REV-002` | 2 FR | 100% Lengkap | Validasi ulasan hanya untuk pembeli terverifikasi (`is_verified_purchase`), status sebagian pada unggah foto, dan command pengingat ulasan. |
| **Admin Backoffice** | `FR-ADM-001` s/d `FR-ADM-005` | 5 FR | 100% Lengkap | Dashboard metrik omzet sah, katalog & varian produk, siklus pesanan & resi ber-email pasca commit, kupon voucher, dan moderasi ulasan. |
| **Total** | **8 Kelompok Modul** | **26 FR** | **100% Lengkap** | **Seluruh 26 FR memiliki Deskripsi, Aktor, Precondition, Main Flow, Alternative Flow, Postcondition, Acceptance Criteria, Status.** |

---

## 6. Informasi yang Tidak Dapat Diverifikasi

1. **Status Deployment pada Lingkungan Production:**  
   Status deployment pada lingkungan production **belum dapat diverifikasi berdasarkan bukti yang tersedia di workspace**. Konfigurasi deployment (`vercel.json`, `launcher.js`, dan `api/index.php`) ditemukan pada repositori, namun keberadaan dan kondisi deployment aktif memerlukan pemeriksaan langsung pada dashboard penyedia hosting Vercel. Oleh sebab itu, tidak boleh diklaim bahwa aplikasi telah berstatus *production-ready* atau *staging-verified*.
2. **Ketersediaan Perangkat Lunak Rendering Diagram Lokal:**  
   Perangkat lunak Java Runtime Environment (JRE) dan PlantUML CLI tidak terpasang pada lingkungan kerja lokal workspace ini. Berdasarkan batasan keamanan yang melarang instalasi dependency tambahan di luar repositori, diagram tidak dirender menjadi berkas biner SVG/PNG lokal. Seluruh sumber diagram dipertahankan dalam format standar PlantUML (`.puml`) yang valid dan siap dirender ulang pada server CI/CD atau lingkungan yang memiliki JRE.

---

## 7. Perbedaan Antara SRS Lama dan Implementasi Aktual

| Aspek / Bagian | Pernyataan pada SRS Lama (`SRS.md`) | Temuan Implementasi Aktual pada Basis Kode | Penyesuaian pada SRS Baru (Bab 1–4) |
|---|---|---|---|
| **Sistematika Dokumen** | Menggunakan 10 Bab gaya internal developer. | Template resmi Universitas Amikom Yogyakarta menggunakan struktur 4 Bab utama. | Dokumen distrukturkan ulang secara ketat menjadi Bab 1 Pendahuluan, Bab 2 Deskripsi Aplikasi, Bab 3 Kebutuhan Eksternal, dan Bab 4 Kebutuhan Fungsional. |
| **Jumlah Kebutuhan Fungsional** | Tercampur tanpa pemisahan aktor dan struktur formal seragam. | Basis kode dan rute memuat 26 fungsi spesifik yang dapat diuji. | Dispesifikasikan secara formal menjadi 26 Functional Requirements (`FR-AUTH-001` s/d `FR-ADM-005`) dengan 8 komponen baku. |
| **Penyimpanan Kredensial** | Menyebutkan "enkripsi password Bcrypt". | Bcrypt merupakan fungsi hashing satu arah adaptif, bukan algoritma enkripsi dua arah. | Diperbaiki menjadi "hashing kata sandi menggunakan Bcrypt" pada seluruh bagian dokumen. |
| **Idempotensi Checkout** | Menggambarkan cache memori sebagai pengaman idempotensi utama. | Konfigurasi serverless menggunakan `CACHE_STORE=array`. Sumber kebenaran tunggal (*single source of truth*) adalah pengecekan baris `orders.checkout_token` di database MySQL. | Diperbaiki pada Bab 2, Bab 4 (FR-CHK-001), dan Sequence Diagram 4.6.1. |
| **Toleransi Nominal Midtrans** | Disebutkan toleransi selisih Rp1. | Kode sumber `PaymentService.php` baris 417 mengonfirmasi: `if (abs((float) $parsed['amount'] - (float) $order->grand_total) > 1.0)`. | Dinyatakan valid: sistem menerapkan toleransi selisih pembulatan hingga Rp1.0. |
| **Parameter Pelacakan Resi** | Disebutkan pelacakan real-time. | `ShipmentTrackingService.php` memanggil `trackWaybill($awb, $courier, substr($phone, -5))` (3 parameter) dan menerapkan jeda cache cooldown 5 menit. | Diperjelas pada batasan produk, FR-SHIP-002, dan Sequence Diagram 4.6.3. |
| **Urutan Pengiriman Email** | Sering diasumsikan berjalan di dalam transaksi database. | Kode sumber membuktikan email (`OrderPlacedMail`, `PaymentReceivedMail`, `ShipmentDispatchedMail`) dikirim **setelah** `DB::commit()` berhasil. | Diperbaiki pada seluruh diagram aktivitas dan sequence diagram. |
| **Notasi Use Case Diagram** | Menggunakan bagan alur non-standar. | Kaidah rekayasa perangkat lunak mewajibkan diagram use case standar UML. | Disusun ulang menggunakan PlantUML dengan relasi Generalization (`Customer -up-|> Guest`), Extend (`UC_ApplyVoucher ..> UC_Checkout`), dan asosiasi aktor eksternal yang tepat. |
| **Domain Class Diagram** | Tidak lengkap atau hanya mencantumkan model parsial. | Repositori memiliki 20 Model Eloquent aktif di `app/Models/`. | Memetakan ke-20 model Eloquent secara lengkap beserta relasi dan metodenya pada diagram PlantUML dan Mermaid. |

---

## 8. Status Automated Testing

Berdasarkan inventarisasi direktori pengujian, terdapat 27 berkas pengujian fitur (*feature tests*) pada direktori `tests/Feature/` dan pengujian unit pada `tests/Unit/`. Berkas-berkas tersebut mencakup:
- `CustomerAuthTest.php`
- `AdminAuthTest.php`
- `PublicCatalogTest.php`
- `CartTest.php`
- `CheckoutTest.php`
- `PaymentTest.php`
- `OrderStateTransitionTest.php`
- `ShipmentTrackingTest.php`
- `ReleaseExpiredOrdersTest.php`
- `VoucherCheckoutTest.php`
- `AdminCategoryTest.php`, `AdminProductTest.php`, `AdminVoucherTest.php`, `AdminDashboardTest.php`, dll.

*Catatan Audit:* Seluruh pengujian dianalisis melalui *static test code review* guna memverifikasi ekspektasi perilaku sistem dan *acceptance criteria*. Pengujian dinamis otomatis tidak dieksekusi secara berulang pada sesi ini untuk mencegah modifikasi terhadap database lokal dan memastikan kepatuhan terhadap aturan pembatasan perubahan workspace.

---

## 9. Informasi yang Membutuhkan Konfirmasi Pemilik Proyek

1. **Aktivasi Fitur Dormant:** Keputusan mengenai entitas tabel `banners`, `notifications`, dan `product_views`—apakah akan diimplementasikan penuh pada pengembangan tahap berikutnya atau dihapus dari skema migrasi jika tidak digunakan.
2. **Prioritas Formulir Unggah Foto Ulasan:** Apakah komponen pengunggahan foto ulasan pembeli perlu segera dibangun dengan alokasi penyimpanan pada Supabase Storage.
3. **Format Rekapitulasi Laporan Keuangan:** Format dokumen baku yang diinginkan oleh pemilik butik untuk rekapitulasi data penjualan bulanan (format spreadsheet Excel `.xlsx` atau dokumen cetak PDF `.pdf`).
