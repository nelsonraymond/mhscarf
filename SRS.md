# Software Requirements Specification (SRS)

# Sistem E-Commerce Butik Hijab & Modest Fashion — MH SCARF

**Nomor Dokumen:** SRS-MHSCARF-2026-V1.0  
**Nama Sistem:** MH SCARF E-Commerce Platform  
**Standar Acuan:** Prinsip IEEE 29148:2018 (Systems and software engineering — Life cycle processes — Requirements engineering)  
**Versi:** 1.0 (Audit Baseline & Source Code Synchronization)  
**Tanggal:** 27 Oktober 2026  
**Status Dokumen:** Approved Baseline  
**Penulis:** Senior Software Engineer, System Analyst, & Database Architect

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen Software Requirements Specification (SRS) ini disusun untuk mendokumentasikan spesifikasi kebutuhan perangkat lunak sistem e-commerce **MH SCARF** secara formal, terstruktur, dan dapat diuji. Dokumen ini merefleksikan kondisi implementasi teknis aktual pada repositori `D:\laragon\www\mutya-store` dan berfungsi sebagai:

1. Acuan baseline bagi tim rekayasa perangkat lunak dalam pemeliharaan dan pengembangan fitur lanjutan.
2. Dasar perancangan skenario pengujian fungsional, integrasi, dan performa (_test cases_).
3. Referensi teknis resmi untuk dokumentasi arsitektur sistem, audit kode, dan laporan pertanggungjawaban proyek.
4. Tolok ukur verifikasi kesenjangan (_gap analysis_) antara kebutuhan bisnis dan kode yang telah diimplementasikan.

### 1.2 Ruang Lingkup Produk

Sistem e-commerce MH SCARF adalah aplikasi berbasis web yang melayani penjualan produk hijab dan aksesori modest fashion secara _Direct-to-Consumer_ (DTC). Ruang lingkup aplikasi mencakup:

- **Sisi Pelanggan (Storefront):** Penjelajahan katalog produk, visualisasi varian warna/ukuran, keranjang belanja, pembelian langsung (_Buy Now_), kalkulasi tarif ongkir domestik real-time, pembayaran daring otomatis melalui Midtrans, pelacakan nomor resi pengiriman ekspedisi, serta pemberian ulasan produk terverifikasi.
- **Sisi Administrator (Backoffice):** Dashboard operasional, manajemen katalog (kategori, produk, varian, dan unggah berkas gambar ke Supabase Storage), pengelolaan stok dan mutasi fisik, pemrosesan status pesanan menggunakan _state machine_, manajemen pengiriman, pengaturan voucher promosi, serta moderasi ulasan pelanggan.
- **Sisi Integrasi Latar Belakang (System Services):** Penanganan webhook pembayaran dengan verifikasi tanda tangan kriptografis, _background scheduler_ untuk pelepasan reservasi stok kedaluwarsa, dan pengiriman email transaksional melalui Resend API.

Aplikasi tidak mencakup transaksi fisik di toko luring (_Point of Sale_), pengiriman internasional di luar wilayah Indonesia, atau sistem multi-vendor (marketplace).

### 1.3 Definisi, Akronim, dan Singkatan

| Istilah / Singkatan      | Definisi                                                                                                                                                |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **AWB**                  | _Air Waybill_; nomor resi pengiriman paket yang diterbitkan oleh kurir ekspedisi.                                                                       |
| **Bcrypt**               | Fungsi _hashing_ kata sandi berbasis algoritma Blowfish yang digunakan untuk keamanan data kredensial.                                                  |
| **CSRF**                 | _Cross-Site Request Forgery_; serangan siber yang dicegah melalui validasi token sesi Laravel.                                                          |
| **DBML**                 | _Database Markup Language_; format deklaratif untuk mendefinisikan struktur skema dan relasi basis data.                                                |
| **DTC**                  | _Direct-to-Consumer_; model bisnis penjualan langsung dari pemilik merek ke pembeli akhir tanpa perantara marketplace.                                  |
| **Idempotensi**          | Karakteristik operasi perangkat lunak di mana eksekusi berulang dengan masukan identik tidak menyebabkan perubahan status tambahan atau duplikasi data. |
| **Komerce / RajaOngkir** | Penyedia layanan API kalkulasi ongkos kirim dan pelacakan pengiriman kurir domestik Indonesia.                                                          |
| **Midtrans Snap**        | Antarmuka pembayaran terintegrasi pihak ketiga berbasis pop-up token yang mendukung VA, E-Wallet, dan QRIS.                                             |
| **NFR**                  | _Non-Functional Requirement_; kebutuhan non-fungsional sistem (keamanan, performa, reliabilitas).                                                       |
| **Resend**               | Layanan penyedia API infrastruktur pengiriman email transaksional.                                                                                      |
| **SKU**                  | _Stock Keeping Unit_; kode alfanumerik unik untuk mengidentifikasi unit produk dan varian secara spesifik.                                              |
| **Socialite**            | Paket resmi Laravel untuk menangani autentikasi pihak ketiga melalui protokol OAuth 2.0 (Google).                                                       |
| **Supabase Storage**     | Layanan penyimpanan objek berbasis cloud yang kompatibel dengan protokol AWS S3 API.                                                                    |
| **TTL**                  | _Time-to-Live_; masa berlaku suatu data sebelum dianggap usang atau kedaluwarsa (misal: reservasi stok 15 menit).                                       |

### 1.4 Referensi

1. IEEE Std 29148-2018: _ISO/IEC/IEEE International Standard — Systems and software engineering — Life cycle processes — Requirements engineering_.
2. Dokumentasi Resmi Laravel 13.x Framework (`https://laravel.com/docs`).
3. Midtrans Core & Snap API Technical Documentation (`https://docs.midtrans.com`).
4. Komerce Shipping Cost V2 / RajaOngkir API Reference (`https://rajaongkir.komerce.id`).
5. Repositori Source Code Aktual MH SCARF (`D:\laragon\www\mutya-store`).
6. Dokumen Arsitektur Basis Data: `ERD-DATABASE.md`.
7. Dokumen Kebutuhan Produk: `PRD Website E-Commerce Hijab.md`.

### 1.5 Gambaran Umum Dokumen

Dokumen ini disusun dalam sepuluh bagian utama: Bab 1 Pendahuluan; Bab 2 Deskripsi Umum Sistem; Bab 3 Kebutuhan Fungsional Spesifik; Bab 4 Kebutuhan Antarmuka Eksternal; Bab 5 Kebutuhan Non-Fungsional; Bab 6 Kebutuhan Data dan Integritas; Bab 7 Aturan Bisnis; Bab 8 Skenario Use Case; Bab 9 Matriks Keterlacakan (Traceability Matrix); dan Bab 10 Analisis Kesenjangan (Gap Analysis) serta Rekomendasi Tindak Lanjut.

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

MH SCARF merupakan aplikasi web monolitik modular (_modular monolith_) yang dibangun menggunakan framework Laravel 13 dan dikonfigurasi untuk berjalan pada lingkungan _cloud serverless_ (Vercel) dengan basis data terkelola (Clever Cloud MySQL) dan penyimpanan berkas terpisah (Supabase Storage).

```
+-----------------------------------------------------------------------------------+
|                            STOREFRONT & ADMIN CLIENT                              |
|               (Responsive Web: Tailwind CSS v4, Blade Templates, Vite 8)          |
+-----------------------------------------------------------------------------------+
                                         | HTTPS (JSON / Form POST)
                                         v
+-----------------------------------------------------------------------------------+
|                            APLIKASI LARAVEL 13 (Vercel)                           |
|  - Routing & Middleware (Auth, Admin, Throttle, TrustHosts)                       |
|  - Controller Layer (Catalog, Cart, Checkout, Order, Payment, Admin)              |
|  - Service Layer (CartService, CheckoutService, OrderService, PaymentService, dll)|
|  - Domain Model & Eloquent ORM                                                    |
+-----------------------------------------------------------------------------------+
        |                      |                    |                  |
        v                      v                    v                  v
+---------------+      +----------------+   +---------------+   +-------------------+
|  CLEVER CLOUD |      | SUPABASE S3    |   | MIDTRANS SNAP |   | KOMERCE / RESEND  |
|  MySQL DB     |      | Storage Bucket |   | Payment GW    |   | Ekspedisi & Email |
+---------------+      +----------------+   +---------------+   +-------------------+
```

### 2.2 Fungsi Utama Sistem

1. **Manajemen Katalog & Penemuan Produk:** Penayangan produk aktif, penelusuran varian warna/ukuran, pencarian teks, pemilahan (_sorting_), serta rekomendasi produk sejenis.
2. **Transaksi & Idempotensi Keranjang/Checkout:** Penanganan keranjang belanja tamu dan pelanggan, penggabungan keranjang saat login, kalkulasi ongkos kirim real-time, reservasi stok atomik 15 menit, dan proteksi submit ganda via UUID checkout token.
3. **Pembayaran Digital & Rekonsiliasi:** Pembuatan token Snap Midtrans dengan sewa database baris (_row lease_), verifikasi webhook kriptografis, transisi status pembayaran, dan sweeper pesanan kedaluwarsa.
4. **Pemenuhan & Pelacakan Pengiriman:** Pemrosesan status pesanan berbasis _state machine_, pengikatan nomor resi AWB, dan pelacakan riwayat perjalanan paket ekspedisi.
5. **Ulasan Pembelian Terverifikasi:** Pemberian ulasan berbintang oleh pembeli terverifikasi dan pembaruan agregat nilai rating produk secara instan.
6. **Operasional & Administrasi Toko:** Pemantauan metrik pada dashboard admin, manajemen katalog dan foto, penyesuaian stok gudang, dan pengelolaan kode kupon voucher.

### 2.3 Karakteristik Pengguna

1. **Pengunjung Tamu (Guest):** Pengguna tanpa akun yang menjelajahi situs. Memerlukan antarmuka yang cepat, jelas, dan dapat menampung keranjang belanja sementara.
2. **Pelanggan Terdaftar (Customer):** Pengguna terautentikasi (manual atau Google OAuth) yang melakukan pemesanan, menyimpan alamat pengiriman, mengelola wishlist, dan memberikan ulasan.
3. **Administrator Toko (Admin/Owner):** Pengguna internal dengan hak istimewa tinggi untuk mengelola katalog, memperbarui status resi pengiriman, mengatur voucher, dan memoderasi konten ulasan.

### 2.4 Lingkungan Operasi

- **Server Eksekusi:** Vercel Serverless Function (PHP 8.3 Runtime via `vercel-php@0.7.4` dan custom bridge `launcher.js`).
- **Basis Data:** Clever Cloud MySQL 8.x (Produksi) / SQLite 3 in-memory (Pengujian & Otomasi Lokal).
- **Penyimpanan Berkas Gambar:** Supabase Storage (S3 API Endpoint di kawasan `ap-southeast-1`).
- **Browser Klien:** Google Chrome, Mozilla Firefox, Apple Safari, Microsoft Edge (versi desktop dan mobile 2 tahun terakhir).
- **Protokol:** Wajib HTTPS dengan TLS 1.3.

### 2.5 Batasan Desain dan Implementasi

1. **Batasan Serverless Cache:** Karena fungsi serverless Vercel bersifat _stateless_ dan multi-instance dengan konfigurasi `CACHE_STORE=array`, mekanisme penguncian atomik tidak dapat mengandalkan cache memori bersama. Seluruh penguncian transaksional kritis (stok, checkout token, dan pembayaran Snap) wajib dikelola melalui transaksi baris basis data relasional (`lockForUpdate()`).
2. **Sesi Berbasis Cookie:** Sesi pengguna menggunakan `SESSION_DRIVER=cookie` pada konfigurasi Vercel agar sesi tetap konsisten antar-panggilan fungsi serverless yang terpisah.
3. **Mata Uang Tunggal:** Seluruh proses moneter hanya mendukung mata uang Rupiah Indonesia (IDR) bulat tanpa komponen sen.
4. **Ketergantungan API Eksternal:** Layanan ongkos kirim dan pelacakan bergantung pada ketersediaan API pihak ketiga RajaOngkir/Komerce; sistem menerapkan mekanisme _circuit breaker_ / cache cooldown 5 menit untuk melindungi kuota API.

### 2.6 Asumsi dan Ketergantungan

- Pihak pengelola toko memelihara akun aktif dan saldo kuota pada Midtrans, Komerce, Supabase, dan Resend.
- Perubahan zona waktu sistem ditetapkan pada Waktu Indonesia Barat (WIB / UTC+7).

---

## 3. Kebutuhan Fungsional (Functional Requirements)

### 3.1 Modul Autentikasi dan Akun Pengguna

#### FR-AUTH-001: Registrasi Akun Pelanggan Manual

- **Deskripsi:** Sistem harus memfasilitasi pembuatan akun pelanggan baru menggunakan nama, alamat email unik, nomor telepon, dan kata sandi yang aman.
- **Aktor:** Guest.
- **Precondition:** Pengguna belum terautentikasi dan berada di halaman `/register`.
- **Main Flow:**
  1. Pengguna memasukkan nama, email, nomor telepon, kata sandi, dan konfirmasi kata sandi.
  2. Sistem memvalidasi format input (email unik, kata sandi minimal 8 karakter).
  3. Sistem mengenkripsi kata sandi menggunakan Bcrypt.
  4. Sistem menyimpan record pengguna dengan `role = 'customer'`.
  5. Sistem secara otomatis mengautentikasi pengguna dan memindahkan isi keranjang belanja tamu ke keranjang akun pengguna.
  6. Sistem mengarahkan pengguna ke halaman utama.
- **Alternative Flow:** Jika email telah terdaftar, sistem menampilkan pesan validasi galat dan membatalkan proses registrasi.
- **Postcondition:** Record pengguna baru tersimpan pada tabel `users`; sesi login aktif.
- **Acceptance Criteria:** Pengguna terdaftar tidak dapat login dengan kata sandi yang salah; email duplikat ditolak dengan status HTTP 422.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CustomerAuthController@register`, Form Request, Model `User`, Migration `create_users_table.php`, Test `CustomerAuthTest.php`.

#### FR-AUTH-002: Login dan Logout Pelanggan

- **Deskripsi:** Sistem harus menyediakan mekanisme autentikasi pelanggan dengan email dan kata sandi, disertai proteksi pembatasan percobaan gagal (_rate limiting_).
- **Aktor:** Guest, Customer.
- **Precondition:** Pengguna memiliki akun aktif pada sistem.
- **Main Flow:**
  1. Pengguna mengirimkan kredensial email dan kata sandi pada rute `POST /login`.
  2. Sistem memeriksa pembatasan laju (_throttle: 5 percobaan per menit_).
  3. Sistem memverifikasi kecocokan hash kata sandi.
  4. Sistem meregenerasi ID sesi untuk mencegah serangan _session fixation_.
  5. Sistem menjalankan `CartService::mergeGuestCart()` untuk menggabungkan belanjaan tamu ke keranjang akun.
  6. Pengguna diarahkan ke rute yang dituju (_intended URL_) atau beranda.
- **Alternative Flow:** Jika kredensial salah, sistem menambah hitung gagal throttle dan mengembalikan respon galat.
- **Postcondition:** Sesi autentikasi terbentuk pada cookie/session; saat logout (`POST /logout`), sesi dicabut dan token sesi diregenerasi.
- **Acceptance Criteria:** Gagal login 5 kali berturut-turut memicu penguncian sementara selama 60 detik (HTTP 429).
- **Status:** **Implemented**.
- **Bukti Teknis:** `CustomerAuthController@store`, `CustomerAuthController@destroy`, Middleware `throttle:5,1`, Test `CustomerAuthTest.php`.

#### FR-AUTH-003: Autentikasi Google OAuth (Socialite)

- **Deskripsi:** Sistem harus memungkinkan pelanggan melakukan login atau pendaftaran instan menggunakan akun Google.
- **Aktor:** Guest.
- **Precondition:** Pengguna memiliki akun Google aktif dengan email terverifikasi.
- **Main Flow:**
  1. Pengguna mengklik tombol "Masuk dengan Google" pada `/auth/google`.
  2. Sistem mengalihkan pengguna ke consent screen Google OAuth 2.0.
  3. Google mengembalikan kode otorisasi ke callback `/auth/google/callback`.
  4. Sistem memverifikasi keabsahan data profil dan status verifikasi email Google.
  5. Jika record `google_id` atau email sudah ada pada akun pelanggan, sistem menautkan akun tersebut.
  6. Jika akun belum ada, sistem membuat akun baru dengan kata sandi acak aman dan `role = 'customer'`.
  7. Sistem mengabungkan keranjang belanja tamu dan mengalihkan pengguna ke beranda.
- **Alternative Flow:** Jika email Google ternyata terdaftar sebagai akun `role = 'admin'`, sistem menolak login dan mengembalikan pesan "Akun ini tidak dapat masuk dengan Google".
- **Postcondition:** Pelanggan berhasil masuk; nilai `google_id` tersimpan pada tabel `users`.
- **Acceptance Criteria:** Akun dengan role admin dicegah secara mutlak masuk melalui pintu Google OAuth.
- **Status:** **Implemented**.
- **Bukti Teknis:** `GoogleAuthController@redirect`, `GoogleAuthController@callback`, `config/services.php (google)`, Test `CustomerAuthTest.php`.

#### FR-AUTH-004: Reset Kata Sandi Pelanggan

- **Deskripsi:** Sistem harus menyediakan alur pemulihan kata sandi melalui pengiriman token tautan sementara ke alamat email pelanggan.
- **Aktor:** Customer (Lupa Password).
- **Precondition:** Email pelanggan terdaftar dalam sistem.
- **Main Flow:**
  1. Pengguna meminta tautan reset pada `POST /forgot-password`.
  2. Sistem membuat token kriptografis berbatas waktu dan menyimpannya pada tabel `password_reset_tokens`.
  3. Sistem mengirimkan email tautan pemulihan.
  4. Pengguna membuka tautan `/reset-password/{token}` dan memasukkan kata sandi baru.
  5. Sistem memperbarui hash kata sandi pada tabel `users` dan menghapus token terkait.
- **Status:** **Implemented**.
- **Bukti Teknis:** `PasswordResetController`, Migration `create_password_reset_tokens_table`.

#### FR-AUTH-005: Autentikasi Khusus Administrator

- **Deskripsi:** Sistem harus memisahkan gerbang login administrator dengan proteksi pembatasan laju tersendiri dan otorisasi middleware ketat.
- **Aktor:** Admin.
- **Precondition:** Akun pengguna memiliki nilai `role = 'admin'`.
- **Main Flow:**
  1. Administrator mengakses rute `/admin/login`.
  2. Administrator memasukkan kredensial pada `POST /admin/login`.
  3. Sistem memverifikasi kredensial di bawah proteksi `throttle:admin-login`.
  4. Sistem memverifikasi bahwa akun bersangkutan memiliki `role === 'admin'`.
  5. Administrator diarahkan ke Dashboard `/admin`.
- **Alternative Flow:** Pengguna berstatus `customer` yang mencoba login pada form admin ditolak dengan pesan otorisasi tidak sah.
- **Status:** **Implemented**.
- **Bukti Teknis:** `AdminAuthController`, Middleware `admin` (`EnsureUserIsAdmin`), Test `AdminAuthTest.php`.

---

### 3.2 Modul Katalog dan Produk

#### FR-CAT-001: Penayangan Katalog Produk Publik (/shop)

- **Deskripsi:** Sistem harus menampilkan daftar hijab yang aktif dengan dukungan filter kategori, pencarian kata kunci, filter diskon, dan pengurutan data.
- **Aktor:** Guest, Customer.
- **Precondition:** Produk dan kategori berstatus `is_active = true`.
- **Main Flow:**
  1. Pengguna membuka rute `/shop` atau `/shop/{category:slug}`.
  2. Sistem menjalankan query dengan _eager loading_ foto utama, kategori, dan varian aktif.
  3. Sistem menerapkan filter kategori, filter teks `q` (nama, deskripsi, bahan), filter diskon `sale=1` (harga coret > harga jual), dan sorting (`newest`, `price_asc`, `price_desc`, `best_seller`).
  4. Sistem menyajikan hasil dalam paginasi 12 item per halaman beserta daftar jumlah produk per kategori.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CatalogController@index`, Route `shop.index`, View `catalog.index`, Test `PublicCatalogTest.php`.

#### FR-PROD-001: Detail Produk dan Pilihan Varian (/shop/produk/{slug})

- **Deskripsi:** Sistem harus menampilkan rincian produk, varian warna/ukuran, ketersediaan stok riil, ulasan terverifikasi, dan produk sejenis.
- **Aktor:** Guest, Customer.
- **Precondition:** Produk berstatus aktif dan slug valid.
- **Main Flow:**
  1. Pengguna membuka URL produk `/shop/produk/{product:slug}`.
  2. Sistem menampilkan nama, deskripsi, jenis material, petunjuk perawatan, galeri foto terurut, serta opsi varian warna dan ukuran.
  3. Sistem menampilkan nilai rating agregat (`rating_avg`), jumlah ulasan (`rating_count`), dan rincian distribusi bintang 1 hingga 5.
  4. Sistem menampilkan hingga 4 produk terkait dari kategori yang sama (_related products_).
  5. Pengguna dapat memilih varian, menentukan kuantitas (maksimal stok varian), lalu mengklik "Tambah ke Keranjang" atau "Beli Sekarang".
- **Alternative Flow:** Jika produk atau kategorinya berstatus `is_active = false`, sistem mengembalikan respon HTTP 404.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CatalogController@show`, Route `shop.product`, View `catalog.show`, Test `PublicCatalogTest.php`.

#### FR-PROD-002: Pembelian Langsung Tanpa Keranjang (Buy Now)

- **Deskripsi:** Sistem harus memungkinkan pembeli memotong alur keranjang belanja dan langsung diarahkan ke halaman checkout untuk varian terpilih.
- **Aktor:** Guest, Customer.
- **Main Flow:**
  1. Pengguna mengklik tombol "Beli Sekarang" pada detail produk dengan varian dan kuantitas tertentu.
  2. Sistem memvalidasi ketersediaan stok varian.
  3. Sistem menyimpan array `buy_now` ke dalam session pengguna (`product_variant_id`, `quantity`).
  4. Sistem mengarahkan pengguna langsung ke `/checkout`.
  5. Pada saat pesanan berhasil terbuat, flag sesi `buy_now` dihapus secara otomatis.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CartController@buyNow`, `CheckoutService::placeOrder`, Test `CheckoutTest.php`.

---

### 3.3 Modul Keranjang Belanja dan Wishlist

#### FR-CART-001: Pengelolaan Item Keranjang Belanja

- **Deskripsi:** Sistem harus mendukung penambahan varian produk ke keranjang, perubahan kuantitas, penghapusan item, dan pengosongan keranjang dengan validasi harga server-side.
- **Aktor:** Guest, Customer.
- **Main Flow:**
  1. Pengguna menambahkan varian produk ke keranjang (`POST /cart/items`).
  2. Sistem memverifikasi stok varian dan menghitung ulang harga unit dari basis data (`finalPrice()`).
  3. Jika varian sudah ada di keranjang, kuantitas digabungkan selama tidak melebihi stok gudang.
  4. Pengguna dapat memperbarui kuantitas via `PATCH /cart/items/{id}` atau menghapus item via `DELETE /cart/items/{id}`.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CartController`, `CartService`, Model `Cart`, Model `CartItem`, Test `CartTest.php`.

#### FR-CART-002: Penggabungan Otomatis Keranjang Tamu (Guest Cart Merge)

- **Deskripsi:** Sistem harus memindahkan item yang dipilih pengguna saat berstatus tamu ke keranjang akun resminya ketika pengguna berhasil login.
- **Aktor:** Guest beralih ke Customer.
- **Main Flow:**
  1. Pengguna memasukkan barang ke keranjang belanja saat belum login (disimpan via `session_id`).
  2. Pengguna melakukan login manual atau via Google OAuth.
  3. `CartService::mergeGuestCart()` mendeteksi ID sesi lama, mencocokkan item, menggabungkan kuantitas pada keranjang `user_id`, lalu menghapus keranjang tamu sementara.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CartService::mergeGuestCart`, `CustomerAuthController`, `GoogleAuthController`, Test `CartCheckoutIntegrationTest.php`.

#### FR-WSH-001: Pengelolaan Daftar Keinginan (Wishlist)

- **Deskripsi:** Sistem harus memungkinkan pelanggan terautentikasi menyimpan dan menghapus produk favorit ke dalam daftar wishlist.
- **Aktor:** Customer.
- **Main Flow:**
  1. Pelanggan mengklik ikon hati pada produk (`POST /wishlist/{product}/toggle`).
  2. Sistem memeriksa relasi pada tabel `wishlists`. Jika sudah ada, sistem menghapusnya; jika belum ada, sistem menyimpannya.
  3. Pelanggan dapat melihat seluruh produk tersimpan pada halaman `/wishlist`.
- **Status:** **Implemented**.
- **Bukti Teknis:** `WishlistController`, Model `Wishlist`, Route `wishlist.index`, Test `WishlistTest.php`.

---

### 3.4 Modul Pengiriman dan Alamat

#### FR-ADDR-001: Pengelolaan Buku Alamat Pelanggan

- **Deskripsi:** Sistem harus menyediakan fasilitas penyimpanan banyak alamat pengiriman per pelanggan serta penetapan satu alamat default.
- **Aktor:** Customer.
- **Main Flow:**
  1. Pelanggan menambah atau mengedit alamat pada `/addresses`.
  2. Sistem memvalidasi kelengkapan nama penerima, nomor HP, detail jalan, kota, provinsi, dan kode pos.
  3. Pelanggan dapat menandai alamat sebagai alamat utama (`POST /addresses/{address}/default`), yang secara otomatis mematikan status default pada alamat lainnya milik pelanggan bersangkutan.
- **Status:** **Implemented**.
- **Bukti Teknis:** `AddressController`, Model `Address`, Test `AddressTest.php`.

#### FR-SHIP-001: Pencarian Wilayah & Perhitungan Ongkos Kirim Real-Time

- **Deskripsi:** Sistem harus mengintegrasikan API Komerce/RajaOngkir V2 untuk mencari destinasi tingkat kecamatan dan menghitung biaya ongkir berdasarkan akumulasi berat paket.
- **Aktor:** Customer.
- **Precondition:** Pelanggan berada di langkah checkout dan memilih alamat pengiriman.
- **Main Flow:**
  1. Frontend mengirim kata kunci kecamatan ke endpoint `POST /checkout/shipping/destinations`.
  2. `RajaOngkirClient::searchDestinations()` mengambil data lokasi terstandarisasi dengan cache 30 menit.
  3. Pelanggan memilih kecamatan tujuan dan kurir yang diinginkan (JNE, POS, TIKI, dll.).
  4. Frontend meminta penawaran ongkir via `POST /checkout/shipping/quotes`.
  5. `ShippingService` menghitung berat total seluruh item pesanan dalam gram, memanggil `/calculate/domestic-cost`, dan mengembalikan daftar layanan (layanan, deskripsi, biaya, estimasi hari tiba) beserta `cart_fingerprint`.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CheckoutShippingController`, `ShippingService`, `RajaOngkirClient`, Test `RajaOngkirShippingTest.php`.

#### FR-SHIP-002: Pelacakan Resi Pengiriman (Waybill Tracking)

- **Deskripsi:** Sistem harus menyajikan riwayat perjalanan paket pengiriman berdasarkan nomor resi AWB yang diinput oleh administrator.
- **Aktor:** Customer, Admin.
- **Precondition:** Pesanan berstatus `shipped` atau `delivered` dan memiliki nomor resi valid.
- **Main Flow:**
  1. Pengguna membuka detail pesanan yang memiliki data resi pengiriman.
  2. `ShipmentTrackingService::refresh()` memanggil API pelacakan `/track/waybill` menggunakan nomor resi, kode kurir, dan 5 digit terakhir nomor HP penerima.
  3. Hasil pelacakan manifest disimpan ke dalam cache dengan masa berlaku panjang, disertai jeda paksa (_rate limit cooldown_) selama 5 menit per resi.
  4. Antarmuka menampilkan riwayat status (waktu, kota, deskripsi kejadian).
- **Status:** **Implemented**.
- **Bukti Teknis:** `ShipmentTrackingService`, `RajaOngkirClient@trackWaybill`, Route `admin.orders.tracking.refresh`, Test `ShipmentTrackingTest.php`.

---

### 3.5 Modul Checkout dan Pembuatan Pesanan

#### FR-CHK-001: Pembuatan Pesanan Terproteksi & Reservasi Stok

- **Deskripsi:** Sistem harus memproses checkout belanja secara transaksional, memotong dan mereservasi stok selama 15 menit, mengaplikasikan voucher, menyimpan snapshot data, serta mencegah submit ganda menggunakan token idempotensi.
- **Aktor:** Customer.
- **Precondition:** Keranjang belanja tidak kosong, alamat pengiriman valid, dan penawaran ongkir terpilih.
- **Main Flow:**
  1. Pelanggan menekan tombol "Buat Pesanan" pada `POST /checkout` dengan membawa `checkout_token` (UUID).
  2. Sistem menjalankan transaksi basis data dengan penguncian baris (`lockForUpdate()`) pada keranjang dan varian produk.
  3. Sistem memverifikasi kembali berat paket dan kecocokan isi keranjang dengan penawaran ongkir.
  4. `StockReservationService::reserveAndDeduct()` memvalidasi stok, mengurangi `stock_qty` varian fisik melalui pencatatan mutasi inventaris, dan membuat record `stock_reservations` dengan status `pending` (kedaluwarsa dalam 15 menit).
  5. Jika terdapat kode voucher, `VoucherService::lockAndEvaluate()` mengunci kuota voucher dan menghitung nilai diskon yang sah.
  6. Sistem membentuk record `orders` dengan nomor pesanan unik, snapshot alamat penerima, status awal `pending`, dan status bayar `pending`.
  7. Sistem membentuk baris `order_items` dengan snapshot permanen nama produk, nama varian, SKU, harga satuan, dan kuantitas, serta mengaitkan ID reservasi stok.
  8. Sistem membentuk record `shipments` relasi 1:1 dan mencatat riwayat pertama pada `order_status_histories`.
  9. Sistem mengosongkan keranjang belanja pengguna dan mengirimkan notifikasi email `OrderPlacedMail`.
  10. Pengguna dialihkan ke halaman instruksi pembayaran `/orders/{order}/payment`.
- **Alternative Flow (Idempotensi):** Jika request dengan `checkout_token` yang sama dikirimkan ulang (akibat pembeli melakukan double-click), sistem mendeteksi duplikasi token dan mengembalikan instance pesanan yang sudah ada tanpa memotong stok ulang.
- **Alternative Flow (Stok Kurang):** Jika stok varian telah diambil pembeli lain sebelum transaksi dikunci, sistem melempar `ValidationException` dan membatalkan transaksi.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CheckoutController@store`, `CheckoutService::placeOrder`, `StockReservationService`, Model `Order`, Model `StockReservation`, Test `CheckoutTest.php`.

---

### 3.6 Modul Pembayaran Digital (Midtrans)

#### FR-PAY-001: Inisialisasi Sesi Pembayaran Midtrans Snap Idempotent

- **Deskripsi:** Sistem harus membuat sesi pembayaran Snap token yang dilindungi secara _durable_ di basis data agar tidak terjadi duplikasi request pada lingkungan serverless.
- **Aktor:** Customer.
- **Precondition:** Pesanan berstatus `pending` dan pembayaran belum lunas.
- **Main Flow:**
  1. Pengguna membuka antarmuka pembayaran `/orders/{order}/payment`.
  2. `PaymentService::initializePayment()` mengunci baris pesanan. Jika ada token Snap yang belum kedaluwarsa, token tersebut digunakan kembali (_reusable_).
  3. Jika membutuhkan sesi baru, sistem membuat klaim pada tabel `payment_initialization_attempts` dengan batas sewa waktu (_lease duration_ 60 detik).
  4. Sistem meminta token transaksi ke API Midtrans Snap dengan batas kedaluwarsa disesuaikan dengan sisa masa reservasi stok.
  5. Sistem menyimpan token pada metadata tabel `payments` dan menandai status attempt menjadi `completed`.
  6. Frontend menampilkan pop-up pembayaran Midtrans Snap.
- **Status:** **Implemented**.
- **Bukti Teknis:** `PaymentController@show`, `PaymentService::initializePayment`, `MidtransPaymentGateway`, Model `PaymentInitializationAttempt`, Test `PaymentTest.php`.

#### FR-PAY-002: Penanganan Webhook Pembayaran Midtrans

- **Deskripsi:** Sistem harus menerima notifikasi HTTP POST dari Midtrans, memverifikasi tanda tangan SHA512, memvalidasi kecocokan nominal tagihan, dan memperbarui status transaksi secara otomatis.
- **Aktor:** Midtrans Payment Gateway (Sistem Eksternal).
- **Precondition:** Webhook dikirim ke URL publik `/webhooks/payment`.
- **Main Flow:**
  1. Webhook masuk membawa payload notifikasi transaksi dan parameter signature.
  2. `MidtransPaymentGateway::verifyWebhook()` menghitung hash SHA512 dari kombinasi `order_id + status_code + gross_amount + ServerKey` dan mencocokkannya dengan signature Midtrans.
  3. `PaymentService::applyGatewayUpdate()` memverifikasi bahwa `gross_amount` sama dengan `grand_total` pesanan (toleransi Rp1).
  4. Sistem mengunci baris pesanan dan pembayaran (`lockForUpdate()`).
  5. Jika status adalah `settlement` atau `capture` (lunas):
     - Status pembayaran diubah menjadi `paid`, status pesanan beralih ke `confirmed`.
     - `StockReservationService::confirmForOrder()` mengubah status reservasi menjadi `reserved`.
     - Sistem mengirimkan email bukti lunas `PaymentReceivedMail`.
  6. Jika status adalah `expire` atau `cancel`:
     - `OrderService::cancelOrder()` membatalkan pesanan dan mengembalikan kuantitas stok varian ke gudang.
- **Alternative Flow (Pembayaran Terlambat pada Pesanan Batal):** Jika notifikasi lunas tiba pada pesanan yang sudah berstatus `cancelled`, sistem **tidak menghidupkan kembali pesanan**. Sistem mencatat flag `metadata->needs_refund = true` agar admin melakukan pengembalian dana manual.
- **Alternative Flow (Idempotensi Notifikasi):** Jika webhook yang sama dikirimkan berulang kali oleh Midtrans, sistem mendeteksi status pesanan sudah sesuai dan langsung mengembalikan respon `already_processed` tanpa mengirimkan email ganda.
- **Status:** **Implemented**.
- **Bukti Teknis:** `PaymentController@webhook`, `PaymentService::handleWebhook`, `PaymentService::applyGatewayUpdate`, Test `PaymentTest.php`.

#### FR-PAY-003: Pembatalan Otomatis Pesanan Kedaluwarsa (Sweeper Job)

- **Deskripsi:** Sistem harus menyediakan perintah terjadwal di latar belakang untuk membatalkan pesanan yang melampaui batas waktu pembayaran dan memulihkan stok tertahan.
- **Aktor:** Scheduler / Artisan Console.
- **Main Flow:**
  1. Perintah `php artisan orders:release-expired` dijalankan secara berkala.
  2. Sistem mencari pesanan `pending` yang batas waktu reservasi stoknya telah terlewati.
  3. Sebelum membatalkan, sistem melakukan _fallback polling_ ke Midtrans API (`fetchStatus()`) untuk memastikan pembeli belum melakukan pembayaran di detik-detik terakhir.
  4. Jika status di gateway terkonfirmasi belum dibayar, pesanan dibatalkan, kuota voucher dikembalikan, dan stok varian dikembalikan ke etalase via `OrderService::cancelOrder()`.
  5. Reservasi yatim (_orphan reservations_ yang tidak terkait item order) turut dibersihkan.
- **Status:** **Implemented**.
- **Bukti Teknis:** `ReleaseExpiredOrders`, `PaymentService::releaseOverdueOrders`, `StockReservationService::releaseExpiredOrphans`, Test `ReleaseExpiredOrdersTest.php`.

---

### 3.7 Modul Ulasan dan Rating Produk

#### FR-REV-001: Pembuatan Ulasan Pembeli Terverifikasi

- **Deskripsi:** Sistem harus mengizinkan pembeli memberikan ulasan rating bintang 1–5 dan komentar teks pada produk yang telah diterima.
- **Aktor:** Customer.
- **Precondition:** Pesanan berstatus `delivered` atau `completed`; item bersangkutan belum pernah diulas.
- **Main Flow:**
  1. Pelanggan membuka rute ulasan `/order-items/{orderItem}/review`.
  2. Pelanggan mengirimkan rating (1–5) dan komentar teks.
  3. `ReviewService::create()` menyimpan record ulasan dengan `is_verified_purchase = true` dan `is_published = true`.
  4. `ReviewService::refreshProductRating()` mengunci baris produk dan memperbarui nilai `rating_avg` serta `rating_count` secara atomik.
- **Status:** **Implemented** (Fitur unggah foto ulasan berstatus **Partially Implemented**).
- **Bukti Teknis:** `ReviewController`, `ReviewService`, Model `Review`, Migration `create_reviews_table.php`.

#### FR-REV-002: Pengingat Ulasan Otomatis Pasca Pengiriman

- **Deskripsi:** Sistem harus mengirimkan email pengingat kepada pelanggan 24 jam setelah pesanan ditandai terkirim jika produk belum diulas.
- **Aktor:** Scheduler Console.
- **Main Flow:**
  1. Perintah `php artisan reviews:send-reminders` dijalankan berkala.
  2. Sistem mencari pesanan berstatus `delivered` atau `completed` dengan `delivered_at <= now() - 24 jam` dan `review_reminded_at` masih bernilai null.
  3. Sistem memilah item yang belum memiliki ulasan, lalu mengirimkan email `ReviewReminderMail`.
  4. Sistem mencatat timestamp `review_reminded_at`.
- **Status:** **Implemented**.
- **Bukti Teknis:** `SendReviewReminders`, `ReviewReminderMail`.

---

### 3.8 Modul Panel Administrasi (Backoffice)

#### FR-ADM-001: Pemantauan Metrik Dashboard Operasional

- **Deskripsi:** Sistem harus menyajikan ringkasan kinerja toko meliputi omzet pembayaran sah, jumlah antrean pesanan, peringatan stok menipis, dan perhatian refund.
- **Aktor:** Admin.
- **Status:** **Implemented**.
- **Bukti Teknis:** `AdminDashboardController@index`, View `admin.dashboard`, Test `AdminDashboardTest.php`.

#### FR-ADM-002: Manajemen Kategori dan Produk Hijab

- **Deskripsi:** Sistem harus mendukung operasi CRUD kategori dan produk hijab, termasuk slug otomatis, status draft/aktif, penandaan featured/best seller, serta penanganan varian multi-warna dan foto pada Supabase Storage.
- **Aktor:** Admin.
- **Status:** **Implemented**.
- **Bukti Teknis:** `CategoryController`, `ProductController`, `ProductVariantController`, `ProductImageController`, Test `AdminCategoryTest.php`, Test `AdminProductTest.php`.

#### FR-ADM-003: Manajemen Pesanan dan Transisi State Machine

- **Deskripsi:** Sistem harus memungkinkan administrator memproses status pesanan sesuai alur resmi, menginput nomor resi pengiriman, membatalkan pesanan, dan mencatat penyelesaian refund.
- **Aktor:** Admin.
- **Status:** **Implemented**.
- **Bukti Teknis:** `Admin\OrderController`, `OrderService`, Test `OrderStateTransitionTest.php`, Test `AdminRefundTest.php`.

#### FR-ADM-004: Manajemen Voucher Promosi

- **Deskripsi:** Sistem harus mendukung pembuatan dan penonaktifan kupon voucher diskon persentase, potongan nominal, dan gratis ongkir dengan batasan kuota.
- **Aktor:** Admin.
- **Status:** **Implemented**.
- **Bukti Teknis:** `Admin\VoucherController`, `VoucherService`, Test `AdminVoucherTest.php`.

#### FR-ADM-005: Moderasi Ulasan Pelanggan

- **Deskripsi:** Sistem harus memungkinkan administrator menyembunyikan atau menampilkan ulasan pembeli (`is_published`).
- **Aktor:** Admin.
- **Status:** **Implemented**.
- **Bukti Teknis:** `Admin\ReviewController@toggle`.

---

## 4. Kebutuhan Antarmuka Eksternal

### 4.1 Antarmuka Pengguna (User Interface)

- Mengikuti brand identity MH SCARF: _soft luxury_, feminin, bersih, dan elegan.
- Palet warna utama: Ivory (`#FFF9F5`), Dusty Rose (`#D98FAF`), Mauve (`#B97897`), Charcoal (`#3A3033`), dan Soft Gold (`#C9A227`).
- Tipografi: Serif display elegan untuk judul utama (_Playfair Display_) dipadukan font sans-serif modern yang mudah dibaca (_Instrument Sans_).
- Komponen interaktif: Tombol pill, transisi halus 200–300 ms, feedback pesan sukses/galat bernada tenang tanpa banner mencolok.

### 4.2 Antarmuka Gateway Pembayaran (Midtrans)

- **Komunikasi:** Protokol HTTPS REST API ke endpoint Midtrans Snap (`https://app.sandbox.midtrans.com/snap/v1/transactions` untuk Sandbox).
- **Format Payload:** JSON berisi `transaction_details` (order_id, gross_amount), `item_details` (nama barang, kuantitas, harga), `customer_details` (nama, email, telepon, alamat kirim), dan konfigurasi kedaluwarsa Snap token.
- **Verifikasi Webhook:** Menerima HTTP POST dari server Midtrans, mengekstrak signature, dan mencocokkan hash SHA512: `hash('sha512', $orderNumber . $statusCode . $grossAmount . $serverKey)`.

### 4.3 Antarmuka Ekspedisi Pengiriman (RajaOngkir / Komerce API V2)

- **Base URL:** `https://rajaongkir.komerce.id/api/v1`.
- **Autentikasi:** Mengirimkan API Key resmi pada header `key: {RAJAONGKIR_API_KEY}`.
- **Endpoint Terintegrasi:**
  - `GET /destination/domestic-destination?search={query}&limit=100`: Pencarian nama kecamatan dan zip code.
  - `POST /calculate/domestic-cost`: Kalkulasi tarif kurir (asal: `origin_id`, tujuan: `destination_id`, berat paket dalam gram, kurir).
  - `POST /track/waybill`: Pelacakan nomor resi ekspedisi (parameter: `awb`, `courier`, `last_phone_number`).

### 4.4 Antarmuka Layanan Email Transaksional (Resend)

- **Driver:** Integrasi REST API via `resend/resend-php` yang dibungkus oleh custom transport `TimedResendTransport` dengan batas sambungan (_connect timeout_) 3 detik dan _request timeout_ 8 detik.
- **Autentikasi:** API Key dikirim melalui header `Authorization: Bearer {RESEND_API_KEY}`.
- **Pesan Transaksional:** Mengirimkan berkas Mailable HTML Blade responsif (`OrderPlacedMail`, `PaymentReceivedMail`, `ShipmentDispatchedMail`, `ReviewReminderMail`).

### 4.5 Antarmuka Penyimpanan Berkas (Supabase Storage)

- **Protokol:** Kompatibilitas AWS S3 V3 (`league/flysystem-aws-s3-v3`).
- **Endpoint:** `SUPABASE_S3_ENDPOINT` dengan bucket `mh-scarf-images` di wilayah `ap-southeast-1`.
- **Format Berkas:** Gambar produk dan kategori dalam ekstensi `jpeg`, `jpg`, `png`, `webp` dengan ukuran maksimum 5 MB. URL publik diakses melalui `SUPABASE_STORAGE_PUBLIC_URL`.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

### 5.1 Keamanan (Security)

- **NFR-SEC-01 (Hash Kredensial):** Seluruh kata sandi pengguna wajib disimpan dalam bentuk hash Bcrypt dengan faktor biaya (_cost factor_) minimal 12 rounds. _(Status: Implemented)_
- **NFR-SEC-02 (Perlindungan Sesi & Cookie):** Cookie sesi wajib memiliki atribut `HttpOnly`, `Secure` (pada HTTPS), dan `SameSite=Lax`. _(Status: Implemented)_
- **NFR-SEC-03 (Proteksi Brute-Force):** Percobaan otentikasi dibatasi oleh middleware throttle (maksimal 5 percobaan gagal per menit). _(Status: Implemented)_
- **NFR-SEC-04 (Pencegahan IDOR & Otorisasi):** Akses ke data pesanan, pembayaran, dan alamat wajib memverifikasi kepemilikan `user_id` di sisi server sebelum menampilkan atau memanipulasi data. _(Status: Implemented)_
- **NFR-SEC-05 (Validasi Unggah Berkas):** Setiap berkas foto produk wajib divalidasi tipe MIME dan ekstensi aslinya di server, serta disimpan dengan nama acak unik di Supabase. _(Status: Implemented)_

### 5.2 Performa (Performance)

- **NFR-PERF-01 (Waktu Respons Halaman):** Halaman publik (beranda dan katalog) ditargetkan memiliki Largest Contentful Paint (LCP) < 2.5 detik pada koneksi 4G standar. _(Status: Implemented pada arsitektur asset Vite)_
- **NFR-PERF-02 (Pencegahan N+1 Query):** Setiap query relasional yang menampilkan daftar produk, pesanan, dan keranjang wajib menggunakan _eager loading_ Eloquent (`with()`). _(Status: Implemented & Terverifikasi)_
- **NFR-PERF-03 (Ukuran Bundel Frontend):** Berkas CSS yang dihasilkan oleh Tailwind CSS v4 tidak melebihi 150 KB sebelum kompresi gzip. _(Status: Implemented - 126 KB actual)_

### 5.3 Reliabilitas dan Konsistensi Transaksi (Reliability)

- **NFR-REL-01 (Idempotensi Checkout):** Percobaan pengiriman formulir checkout berulang dengan `checkout_token` yang sama tidak boleh menduplikasi baris pesanan atau mengurangi stok dua kali. _(Status: Implemented & Teruji)_
- **NFR-REL-02 (Ketahanan Pembayaran Serverless):** Pembuatan token Snap Midtrans wajib mengandalkan kunci sewa database (_database lease lock_) pada tabel `payment_initialization_attempts` untuk mencegah _race condition_ antar-instance Vercel. _(Status: Implemented & Teruji)_
- **NFR-REL-03 (Isolasi Kegagalan Email):** Kegagalan jaringan atau habisnya kuota pengiriman email Resend tidak boleh membatalkan (_rollback_) transaksi checkout atau konfirmasi pembayaran yang sah. _(Status: Implemented via try-catch logging)_

### 5.4 Kompatibilitas dan Portabilitas (Compatibility)

- **NFR-COMP-01 (Dukungan Browser):** Antarmuka web harus beroperasi tanpa cacat tata letak pada browser modern desktop dan perangkat bergerak (Chrome, Safari, Firefox, Edge). _(Status: Implemented)_
- **NFR-COMP-02 (Dukungan Serverless Platform):** Aplikasi harus kompatibel dengan lingkungan serverless Vercel melalui custom adapter `api/index.php` dan `launcher.js`. _(Status: Implemented)_

---

## 6. Kebutuhan Data dan Integritas

### 6.1 Model Data Relasional

Struktur data relasional MH SCARF terdiri dari 23 entitas domain aktif dan 4 entitas sistem pendukung framework. Diagram relasi entitas didefinisikan secara lengkap pada dokumen [ERD-DATABASE.md](file:///d:/laragon/www/mutya-store/ERD-DATABASE.md).

### 6.2 Integritas Transaksi dan Pencegahan Overselling

1. **Penguncian Dua Tahap (_Two-Phase Locking_):** Ketika transaksi checkout dimulai, baris keranjang dan seluruh varian produk yang bersangkutan dikunci menggunakan `lockForUpdate()`.
2. **Reservasi Stok Berdurasi:** Kuantitas stok langsung dipotong dari `product_variants.stock_qty` dan dicatat ke `stock_reservations` dengan status `pending` dan TTL 15 menit.
3. **Pelepasan Stok Otomatis:** Perintah `orders:release-expired` bertindak sebagai _garbage collector_ yang mengembalikan stok varian ke etalase toko jika waktu sewa habis tanpa adanya pembayaran.

### 6.3 Perlindungan Histori Transaksi (Snapshot Integrity)

Setiap pesanan yang terbentuk wajib menyalin secara permanen (_snapshot_) nilai harga satuan, nama produk, nama varian, kode SKU, detail alamat tujuan, dan tarif ekspedisi ke dalam baris `order_items` dan `shipments`. Perubahan nama atau harga produk di masa depan tidak diperbolehkan mengubah riwayat pesanan yang sudah tercatat.

### 6.4 Kepatuhan Perlindungan Data Pribadi (UU PDP)

Data kontak, nomor telepon, dan alamat pelanggan hanya digunakan untuk keperluan pengiriman ekspedisi dan email transaksi. Informasi pembayaran kartu dilindungi melalui tokenisasi pihak ketiga berstandar PCI-DSS tanpa penyimpanan kredensial pada server internal.

---

## 7. Aturan Bisnis (Business Rules)

| ID        | Nama Aturan                     | Pernyataan Aturan Bisnis                                                                                                                                                                                                                                              |
| --------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **BR-01** | Kalkulasi Harga Varian          | Harga final suatu varian hijab dihitung secara server-side dari rumus: `Product.base_price + ProductVariant.additional_price`. Masukan harga dari sisi klien tidak pernah dipercaya.                                                                                  |
| **BR-02** | Batas Kuantitas Pembelian       | Jumlah unit varian yang dimasukkan ke keranjang atau checkout tidak boleh melebihi sisa unit fisik yang tersedia pada `ProductVariant.stock_qty`.                                                                                                                     |
| **BR-03** | Penahanan Stok Pembayaran       | Stok varian dipotong seketika saat pesanan dibuat dan ditahan selama 15 menit. Jika pembayaran tidak diverifikasi dalam tenggat waktu tersebut, pesanan dibatalkan dan stok dipulihkan.                                                                               |
| **BR-04** | State Machine Siklus Pesanan    | Transisi status pesanan hanya diperbolehkan maju searah: `pending` &rarr; `confirmed` &rarr; `processing` &rarr; `packed` &rarr; `shipped` &rarr; `delivered` &rarr; `completed`. Status `cancelled` hanya dapat diterapkan jika pesanan belum masuk tahap `shipped`. |
| **BR-05** | Syarat Pengiriman Pesanan       | Admin hanya diizinkan mengubah status pesanan menjadi `shipped` jika pesanan telah lunas (`payment_status = 'paid'`) serta nama kurir, layanan, dan nomor resi pengiriman AWB terisi lengkap.                                                                         |
| **BR-06** | Validasi Voucher Belanja        | Satu transaksi pesanan hanya berhak menggunakan maksimal 1 kupon voucher. Diskon tidak boleh melebihi total subtotal (atau ongkir untuk voucher gratis ongkir). Kuota voucher dikunci atomik saat checkout dan dipulihkan bila pesanan batal.                         |
| **BR-07** | Penanganan Pembayaran Terlambat | Pembayaran yang masuk melalui gateway pada pesanan yang sudah terlanjur berstatus `cancelled` tidak akan menghidupkan pesanan kembali, melainkan ditandai untuk pengembalian dana manual (`needs_refund = true`).                                                     |
| **BR-08** | Syarat Ulasan Pembeli           | Ulasan produk hanya dapat dibuat oleh customer yang bersangkutan jika pesanan sudah berstatus `delivered` atau `completed`. Satu baris item pesanan hanya berhak atas satu ulasan.                                                                                    |

---

## 8. Skenario Use Case

### 8.1 Skenario UC-01: Customer Melakukan Pembelian Hijab (Checkout & Bayar)

- **Aktor:** Customer, Sistem MH SCARF, Midtrans Gateway.
- **Kondisi Awal:** Pelanggan telah memiliki akun, masuk ke sistem, dan memilih varian hijab ke keranjang belanja.
- **Skenario Normal:**
  1. Pelanggan membuka halaman `/checkout`.
  2. Pelanggan memilih salah satu alamat tersimpan dan memilih opsi kurir pengiriman yang diinginkan.
  3. Pelanggan dapat memasukkan kode voucher diskon dan melihat pratinjau potongan harga.
  4. Pelanggan menekan tombol "Buat Pesanan".
  5. Sistem mengunci baris stok varian, memotong stok gudang, mencatat reservasi stok berdurasi 15 menit, dan menerbitkan pesanan baru berstatus `pending`.
  6. Sistem menyajikan antarmuka pembayaran Midtrans Snap.
  7. Pelanggan menyelesaikan pembayaran melalui QRIS atau Virtual Account pada antarmuka Snap.
  8. Midtrans mengirimkan webhook pemberitahuan ke server MH SCARF.
  9. Sistem memverifikasi signature webhook, mengubah status pembayaran menjadi `paid`, mengubah status pesanan menjadi `confirmed`, menetapkan status reservasi stok menjadi `reserved`, dan mengirim email konfirmasi pembayaran ke pelanggan.
- **Skenario Alternatif:**
  - _Pembayaran Kedaluwarsa:_ Jika pelanggan tidak membayar dalam 15 menit, webhook expire tiba atau scheduler sweeper membatalkan pesanan, melepas reservasi stok, dan mengembalikan unit barang ke etalase toko.

### 8.2 Skenario UC-02: Administrator Memenuhi Pesanan (Fulfillment & Resi)

- **Aktor:** Administrator Toko.
- **Kondisi Awal:** Pesanan berstatus `confirmed` (sudah lunas).
- **Skenario Normal:**
  1. Administrator membuka daftar pesanan pada menu `/admin/orders`.
  2. Administrator mengubah status pesanan menjadi `processing` saat barang mulai disiapkan dari gudang.
  3. Setelah hijab dikemas rapi, Administrator mengubah status menjadi `packed`.
  4. Administrator menyerahkan paket ke kurir ekspedisi dan memperoleh nomor resi pengiriman AWB.
  5. Administrator membuka form pengiriman pesanan, memilih kurir, memasukkan nomor resi, dan menekan simpan.
  6. Sistem mengubah status pesanan menjadi `shipped`, memperbarui status shipment, dan otomatis mengirimkan email notifikasi pengiriman beserta nomor resi ke email pembeli.

---

## 9. Matriks Keterlacakan (Traceability Matrix)

Matriks berikut memetakan setiap kebutuhan fungsional terhadap referensi PRD, komponen kode implementasi, entitas basis data, pengujian otomatis, dan status verifikasi aktual:

| ID Kebutuhan    | Fitur PRD     | Rute / Controller / Service                                                                     | Tabel ERD Terkait                                              | Verifikasi Test Otomatis                              | Status Implementasi                          |
| --------------- | ------------- | ----------------------------------------------------------------------------------------------- | -------------------------------------------------------------- | ----------------------------------------------------- | -------------------------------------------- |
| **FR-AUTH-001** | F-11 Akun     | `CustomerAuthController@register`                                                               | `users`                                                        | `CustomerAuthTest.php`                                | **Implemented**                              |
| **FR-AUTH-002** | F-11 Akun     | `CustomerAuthController@store`                                                                  | `users`, `sessions`                                            | `CustomerAuthTest.php`                                | **Implemented**                              |
| **FR-AUTH-003** | F-11 Akun     | `GoogleAuthController`                                                                          | `users`                                                        | `CustomerAuthTest.php`                                | **Implemented**                              |
| **FR-AUTH-004** | F-11 Akun     | `PasswordResetController`                                                                       | `password_reset_tokens`, `users`                               | Manual / Web Test                                     | **Implemented**                              |
| **FR-AUTH-005** | F-18 Admin    | `AdminAuthController`, Middleware `admin`                                                       | `users`                                                        | `AdminAuthTest.php`                                   | **Implemented**                              |
| **FR-CAT-001**  | F-02 Shop     | `CatalogController@index`                                                                       | `categories`, `products`, `product_variants`                   | `PublicCatalogTest.php`                               | **Implemented**                              |
| **FR-PROD-001** | F-04 Detail   | `CatalogController@show`                                                                        | `products`, `product_variants`, `product_images`, `reviews`    | `PublicCatalogTest.php`                               | **Implemented**                              |
| **FR-PROD-002** | F-04 Detail   | `CartController@buyNow`, `CheckoutService`                                                      | `products`, `product_variants`                                 | `CheckoutTest.php`                                    | **Implemented**                              |
| **FR-CART-001** | F-07 Cart     | `CartController`, `CartService`                                                                 | `carts`, `cart_items`, `product_variants`                      | `CartTest.php`                                        | **Implemented**                              |
| **FR-CART-002** | F-07 Cart     | `CartService::mergeGuestCart`                                                                   | `carts`, `cart_items`                                          | `CartCheckoutIntegrationTest.php`                     | **Implemented**                              |
| **FR-WSH-001**  | F-06 Wishlist | `WishlistController`                                                                            | `wishlists`, `products`                                        | `WishlistTest.php`                                    | **Implemented**                              |
| **FR-ADDR-001** | F-11 Akun     | `AddressController`                                                                             | `addresses`, `users`                                           | `AddressTest.php`                                     | **Implemented**                              |
| **FR-SHIP-001** | F-08 Checkout | `CheckoutShippingController`, `RajaOngkirClient`                                                | `shipments`                                                    | `RajaOngkirShippingTest.php`                          | **Implemented**                              |
| **FR-SHIP-002** | F-10 Tracking | `ShipmentTrackingService`, `AdminOrderController`                                               | `shipments`, `orders`                                          | `ShipmentTrackingTest.php`                            | **Implemented**                              |
| **FR-CHK-001**  | F-08 Checkout | `CheckoutController`, `CheckoutService`, `StockReservationService`                              | `orders`, `order_items`, `stock_reservations`, `shipments`     | `CheckoutTest.php`, `StockReservationTest.php`        | **Implemented**                              |
| **FR-PAY-001**  | F-09 Payment  | `PaymentController@show`, `PaymentService`                                                      | `payments`, `payment_initialization_attempts`, `orders`        | `PaymentTest.php`                                     | **Implemented**                              |
| **FR-PAY-002**  | F-09 Payment  | `PaymentController@webhook`, `MidtransPaymentGateway`                                           | `payments`, `orders`, `stock_reservations`                     | `PaymentTest.php`                                     | **Implemented**                              |
| **FR-PAY-003**  | F-09 Payment  | `ReleaseExpiredOrders`, `PaymentService`                                                        | `orders`, `stock_reservations`, `inventory_movements`          | `ReleaseExpiredOrdersTest.php`                        | **Implemented**                              |
| **FR-REV-001**  | F-05 Review   | `ReviewController`, `ReviewService`                                                             | `reviews`, `products`, `order_items`                           | `DatabaseFoundationTest.php`                          | **Partially Implemented** (Foto belum aktif) |
| **FR-REV-002**  | F-05 Review   | `SendReviewReminders`                                                                           | `orders`, `order_items`, `reviews`                             | Code review terverifikasi                             | **Implemented**                              |
| **FR-VOU-001**  | F-12 Voucher  | `VoucherService`, `Admin\VoucherController`                                                     | `vouchers`, `voucher_usages`                                   | `VoucherCheckoutTest.php`, `AdminVoucherTest.php`     | **Implemented**                              |
| **FR-ADM-001**  | F-15 Admin    | `AdminDashboardController`                                                                      | `orders`, `products`, `product_variants`                       | `AdminDashboardTest.php`                              | **Implemented**                              |
| **FR-ADM-002**  | F-16 Admin    | `CategoryController`, `ProductController`, `ProductVariantController`, `ProductImageController` | `categories`, `products`, `product_variants`, `product_images` | `AdminCategoryTest.php`, `AdminProductTest.php`       | **Implemented**                              |
| **FR-ADM-003**  | F-18 Admin    | `Admin\OrderController`, `OrderService`                                                         | `orders`, `shipments`, `order_status_histories`                | `OrderStateTransitionTest.php`, `AdminRefundTest.php` | **Implemented**                              |
| **FR-ADM-004**  | F-20 Admin    | `Admin\VoucherController`                                                                       | `vouchers`                                                     | `AdminVoucherTest.php`                                | **Implemented**                              |
| **FR-ADM-005**  | F-20 Admin    | `Admin\ReviewController`                                                                        | `reviews`, `products`                                          | Code review terverifikasi                             | **Implemented**                              |

---

## 10. Analisis Kesenjangan (Gap Analysis) dan Rekomendasi Tindak Lanjut

### 10.1 Ringkasan Kesenjangan Implementasi

Berdasarkan audit komparatif antara dokumentasi awal, kebutuhan formal, dan basis kode repositori aktual:

1. **Fitur yang Telah Terpenuhi Penuh (Implemented):**
   - Arsitektur katalog produk, varian, dan galeri Supabase Storage.
   - Autentikasi lengkap (manual, proteksi role, dan Google OAuth Socialite).
   - Keranjang belanja, integrasi sesi tamu, dan alur direct checkout (_Buy Now_).
   - Checkout aman dengan token idempotensi dan reservasi stok berdurasi 15 menit.
   - Integrasi tarif pengiriman domestik RajaOngkir/Komerce V2 dan pelacakan resi AWB.
   - Alur pembayaran Midtrans Snap dengan sewa database baris (_durable lease lock_), verifikasi signature webhook, dan pemulihan stok otomatis.
   - Pengiriman email transaksional via Resend API untuk siklus pesanan utama.
   - Panel administrasi operasional lengkap (dashboard, kategori, produk, varian, pesanan, voucher, ulasan).
2. **Fitur yang Terpenuhi Sebagian (Partially Implemented):**
   - **Unggah Foto Ulasan Pembeli:** Kolom database `photo_path` sudah tersedia dan didukung oleh schema migrasi, namun antarmuka formulir ulasan pada sisi pembeli belum menyediakan input berkas unggah ke Supabase Storage.
   - **Audit Log Mutasi Stok:** Pencatatan mutasi fisik pada tabel `inventory_movements` berjalan di tingkat service, namun belum tersedia halaman tabel khusus pada panel admin untuk melihat riwayat mutasi stok.
3. **Fitur yang Belum Tersedia / Dormant (Planned):**
   - **Banner Promosi Dinamis:** Tabel `banners` dan model `Banner` sudah dibuat dalam migrasi awal, tetapi beranda saat ini menggunakan konten statis dan belum tersedia controller admin untuk mengelola banner secara dinamis.
   - **Notifikasi In-App:** Tabel `notifications` dan model tersedia, namun belum ada antarmuka kotak masuk notifikasi di dashboard pelanggan.
   - **Pencatatan View Produk:** Tabel `product_views` tersedia dalam migrasi, namun tidak ada pemanggilan pencatatan log pada `CatalogController@show`.
   - **Ekspor Laporan Keuangan:** Panel admin menyajikan data metrik, namun fitur ekspor berkas ke format PDF atau Excel belum dibuat.

### 10.2 Rekomendasi Tindak Lanjut Teknis

1. **Penyempurnaan Formulir Ulasan:** Menambahkan komponen unggah berkas gambar (JPG/PNG/WebP, maksimal 2 MB) pada halaman review pembeli dan mengintegrasikannya dengan disk Supabase Storage.
2. **Penyediaan Antarmuka Mutasi Stok Admin:** Membangun rute `/admin/inventory` yang menampilkan pergerakan stok dari tabel `inventory_movements` (kapan terjadi, jenis mutasi, kuantitas, referensi pesanan, dan operator penanggung jawab).
3. **Pembersihan Skema Basis Data:** Untuk tabel dormant (`banners`, `notifications`, `product_views`), disarankan salah satu dari dua langkah: segera implementasikan pengontrolnya pada Fase 2, atau tandai sebagai modul terencana agar tidak menimbulkan persepsi fitur telah selesai.
4. **Penerapan Fulltext Search:** Mempersiapkan indeks `FULLTEXT` pada kolom nama dan deskripsi produk di database Clever Cloud MySQL menjelang peluncuran katalog skala besar.
