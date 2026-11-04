# ERD & Database Design — MH SCARF E-Commerce

**Brand:** MH SCARF / MH Scarf Store  
**Stack Implementasi:** Laravel 13 (PHP 8.3/8.5) + MySQL (Clever Cloud) / SQLite (Local & Testing) + Tailwind CSS v4 + Vite 8  
**Storage & Integrasi:** Supabase Storage (S3 API), Midtrans Snap Sandbox, RajaOngkir / Komerce API V2, Resend Email API, Google OAuth (Laravel Socialite)  
**Deployment Target:** Vercel Serverless (`api/index.php`, custom launcher, cookie session, array cache)  
**Dokumentasi Versi:** 2.0 (Disinkronkan dengan Source Code Aktual Repository per Oktober 2026)

---

## 1. Arsitektur & Prinsip Desain Database

Database sistem e-commerce MH SCARF dirancang untuk mendukung operasional retail busana muslim / hijab secara terukur, konsisten, dan tahan terhadap masalah konkurensi (seperti *race condition* stok dan duplikasi pembayaran).

### Prinsip Desain Aktual:

1. **Normalized Relational Schema:** Memisahkan entitas katalog (`categories`, `products`), varian produk (`product_variants`), aset visual (`product_images`), serta audit fisik (`inventory_movements`).
2. **Immutable Snapshots pada Transaksi:** Tabel `order_items` menyimpan snapshot permanen (`product_name`, `variant_name`, `sku`, `unit_price`, `quantity`, `subtotal`). Perubahan harga atau penamaan di katalog tidak akan mengubah riwayat transaksi lampau.
3. **Konkurensi & *Stock Reservation Lifecycle*:** Reservasi stok sementara dikelola via tabel `stock_reservations` dengan *time-to-live* (TTL default 15 menit). Pengurangan dan pemulihan stok dicatat pada `inventory_movements` untuk keterlacakan mutasi barang.
4. **Idempotensi Checkout & Pembayaran:** 
   - Transaksi checkout dilindungi oleh `checkout_token` (UUID) dan `checkout_fingerprint` pada tabel `orders`.
   - Inisialisasi token pembayaran Snap Midtrans dilindungi secara *durable* oleh baris `payment_initialization_attempts` dengan sewa (*lease*) berbasis waktu di level database, menggantikan *cache lock* yang tidak tersinkronisasi pada lingkungan serverless Vercel multi-instance.
5. **Audit Trail Status Pesanan:** Setiap transisi siklus hidup pesanan dicatat ke dalam `order_status_histories` dengan pencatatan status asal, status tujuan, alasan transisi, dan aktor (customer, admin, atau sistem/sweeper).
6. **Keamanan & Kepatuhan Data:** Tidak ada data sensitif kartu kredit yang disimpan di database internal. Seluruh pembayaran diproses melalui tokenisasi pihak ketiga (Midtrans). Password pengguna di-hash menggunakan algoritma Bcrypt.

---

## 2. Entity Relationship Diagram (DBML)

Format DBML (Database Markup Language) di bawah ini valid dan dapat langsung ditempelkan ke [dbdiagram.io](https://dbdiagram.io) untuk visualisasi diagram grafis.

```dbml
// ==========================================
// MH SCARF E-COMMERCE DATABASE SCHEMA (DBML)
// Status: Actual Implementation (October 2026)
// ==========================================

Table users {
  id bigint [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [unique, not null]
  email_verified_at datetime [null]
  password varchar(255) [not null]
  phone varchar(255) [null]
  role varchar(20) [not null, default: 'customer', note: 'customer | admin']
  google_id varchar(255) [unique, null]
  remember_token varchar(100) [null]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    email [unique]
    google_id [unique]
  }
}

Table categories {
  id bigint [pk, increment]
  parent_id bigint [null, ref: > categories.id]
  name varchar(255) [not null]
  slug varchar(255) [unique, not null]
  description text [null]
  image varchar(255) [null]
  is_active boolean [not null, default: true]
  created_at datetime [null]
  updated_at datetime [null]
  deleted_at datetime [null]

  Indexes {
    slug [unique]
  }
}

Table products {
  id bigint [pk, increment]
  category_id bigint [not null, ref: > categories.id]
  name varchar(255) [not null]
  slug varchar(255) [unique, not null]
  sku varchar(255) [unique, not null]
  description text [null]
  material varchar(255) [null]
  care_instructions text [null]
  base_price decimal(12,2) [not null]
  compare_at_price decimal(12,2) [null]
  is_featured boolean [not null, default: false]
  is_best_seller boolean [not null, default: false]
  is_active boolean [not null, default: true]
  rating_avg decimal(3,2) [not null, default: 0.00]
  rating_count integer [not null, default: 0]
  created_at datetime [null]
  updated_at datetime [null]
  deleted_at datetime [null]

  Indexes {
    slug [unique]
    sku [unique]
    is_active
    is_featured
    is_best_seller
  }
}

Table product_variants {
  id bigint [pk, increment]
  product_id bigint [not null, ref: > products.id]
  name varchar(255) [not null]
  sku varchar(255) [unique, not null]
  color_name varchar(255) [null]
  color_hex varchar(255) [null]
  size varchar(255) [null]
  additional_price decimal(12,2) [not null, default: 0.00]
  stock_qty integer [not null, default: 0]
  low_stock_threshold integer [not null, default: 5]
  weight_gram integer [null]
  is_active boolean [not null, default: true]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    sku [unique]
    stock_qty
    color_name
  }
}

Table product_images {
  id bigint [pk, increment]
  product_id bigint [not null, ref: > products.id]
  variant_id bigint [null, ref: > product_variants.id]
  image_path varchar(255) [not null]
  alt_text varchar(255) [null]
  sort_order integer [not null, default: 0]
  is_primary boolean [not null, default: false]
  created_at datetime [null]
  updated_at datetime [null]
}

Table inventory_movements {
  id bigint [pk, increment]
  product_variant_id bigint [not null, ref: > product_variants.id]
  type varchar(20) [not null, note: 'in | out | adjustment | return']
  quantity integer [not null]
  reference_type varchar(255) [null]
  reference_id bigint [null]
  note varchar(255) [null]
  created_by bigint [null, ref: > users.id]
  created_at datetime [not null]
}

Table carts {
  id bigint [pk, increment]
  user_id bigint [unique, null, ref: > users.id]
  session_id varchar(255) [unique, null]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    user_id [unique]
    session_id [unique]
  }
}

Table cart_items {
  id bigint [pk, increment]
  cart_id bigint [not null, ref: > carts.id]
  product_variant_id bigint [not null, ref: > product_variants.id]
  quantity integer [not null]
  unit_price decimal(12,2) [not null]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    (cart_id, product_variant_id) [unique]
  }
}

Table wishlists {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  product_id bigint [not null, ref: > products.id]
  created_at datetime [not null]

  Indexes {
    (user_id, product_id) [unique]
  }
}

Table addresses {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  label varchar(255) [null]
  recipient_name varchar(255) [not null]
  phone varchar(255) [not null]
  address_line text [not null]
  village varchar(255) [null]
  district varchar(255) [null]
  city varchar(255) [not null]
  province varchar(255) [not null]
  postal_code varchar(20) [not null]
  notes varchar(255) [null]
  is_default boolean [not null, default: false]
  created_at datetime [null]
  updated_at datetime [null]
}

Table orders {
  id bigint [pk, increment]
  order_number varchar(255) [unique, not null]
  user_id bigint [not null, ref: > users.id]
  status varchar(20) [not null, default: 'pending', note: 'pending | confirmed | processing | packed | shipped | delivered | completed | cancelled']
  payment_status varchar(20) [not null, default: 'pending', note: 'pending | paid | failed | expired | refunded']
  subtotal decimal(12,2) [not null]
  discount_amount decimal(12,2) [not null, default: 0.00]
  shipping_cost decimal(12,2) [not null, default: 0.00]
  grand_total decimal(12,2) [not null]
  voucher_code varchar(255) [null]
  voucher_discount decimal(12,2) [not null, default: 0.00]
  shipping_recipient_name varchar(255) [not null]
  shipping_phone varchar(255) [not null]
  shipping_address text [not null]
  shipping_city varchar(255) [not null]
  shipping_province varchar(255) [not null]
  shipping_postal_code varchar(20) [not null]
  customer_note text [null]
  placed_at datetime [null]
  delivered_at datetime [null]
  review_reminded_at datetime [null]
  checkout_token varchar(255) [null]
  checkout_fingerprint varchar(255) [null]
  checkout_cart_fingerprint varchar(255) [null]
  checkout_source varchar(50) [null, note: 'cart | buy_now']
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    order_number [unique]
    (user_id, checkout_token) [unique]
    status
    payment_status
    created_at
  }
}

Table order_items {
  id bigint [pk, increment]
  order_id bigint [not null, ref: > orders.id]
  product_id bigint [not null, ref: > products.id]
  product_variant_id bigint [not null, ref: > product_variants.id]
  product_name varchar(255) [not null]
  variant_name varchar(255) [not null]
  sku varchar(255) [not null]
  unit_price decimal(12,2) [not null]
  quantity integer [not null]
  subtotal decimal(12,2) [not null]
  created_at datetime [null]
  updated_at datetime [null]
}

Table order_status_histories {
  id bigint [pk, increment]
  order_id bigint [not null, ref: > orders.id]
  from_status varchar(50) [null]
  to_status varchar(50) [not null]
  reason varchar(255) [null]
  actor_id bigint [null, ref: > users.id]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    (order_id, id)
  }
}

Table stock_reservations {
  id bigint [pk, increment]
  order_item_id bigint [null, ref: > order_items.id]
  product_variant_id bigint [not null, ref: > product_variants.id]
  quantity integer [not null]
  status varchar(20) [not null, default: 'pending', note: 'pending | reserved | released | cancelled']
  expires_at datetime [not null]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    status
  }
}

Table payments {
  id bigint [pk, increment]
  order_id bigint [not null, ref: > orders.id]
  provider varchar(50) [not null]
  payment_method varchar(50) [null]
  transaction_reference varchar(255) [null]
  amount decimal(12,2) [not null]
  status varchar(20) [not null, default: 'pending', note: 'pending | paid | failed | expired | refunded']
  paid_at datetime [null]
  metadata json [null]
  created_at datetime [null]
  updated_at datetime [null]
}

Table payment_initialization_attempts {
  id bigint [pk, increment]
  order_id bigint [not null, ref: > orders.id]
  attempt_number integer [not null]
  idempotency_key varchar(255) [unique, not null]
  request_fingerprint varchar(255) [not null]
  status varchar(50) [not null, default: 'processing', note: 'processing | completed | retryable | ambiguous']
  lease_expires_at datetime [null]
  payment_id bigint [null, ref: > payments.id]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    idempotency_key [unique]
    (order_id, attempt_number) [unique]
    (order_id, status)
  }
}

Table shipments {
  id bigint [pk, increment]
  order_id bigint [unique, not null, ref: - orders.id]
  courier varchar(255) [not null]
  courier_code varchar(50) [null]
  service varchar(255) [null]
  tracking_number varchar(255) [null]
  shipping_cost decimal(12,2) [not null, default: 0.00]
  destination_id integer [null]
  weight_gram integer [null]
  estimated_delivery varchar(100) [null]
  status varchar(50) [not null, default: 'pending', note: 'pending | shipped | delivered']
  shipped_at datetime [null]
  delivered_at datetime [null]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    order_id [unique]
  }
}

Table reviews {
  id bigint [pk, increment]
  product_id bigint [not null, ref: > products.id]
  user_id bigint [not null, ref: > users.id]
  order_item_id bigint [unique, not null, ref: - order_items.id]
  rating integer [not null]
  title varchar(255) [null]
  comment text [null]
  photo_path varchar(255) [null]
  is_verified_purchase boolean [not null, default: false]
  is_published boolean [not null, default: true]
  created_at datetime [null]
  updated_at datetime [null]
  deleted_at datetime [null]

  Indexes {
    order_item_id [unique]
  }
}

Table vouchers {
  id bigint [pk, increment]
  code varchar(100) [unique, not null]
  type varchar(30) [not null, note: 'percentage | fixed | free_shipping']
  value decimal(12,2) [not null]
  min_order_amount decimal(12,2) [null]
  max_discount_amount decimal(12,2) [null]
  usage_limit integer [null]
  usage_limit_per_user integer [null]
  used_count integer [not null, default: 0]
  starts_at datetime [null]
  expires_at datetime [null]
  is_active boolean [not null, default: true]
  created_at datetime [null]
  updated_at datetime [null]

  Indexes {
    code [unique]
    is_active
    starts_at
    expires_at
  }
}

Table voucher_usages {
  id bigint [pk, increment]
  voucher_id bigint [not null, ref: > vouchers.id]
  user_id bigint [not null, ref: > users.id]
  order_id bigint [not null, ref: > orders.id]
  discount_amount decimal(12,2) [not null]
  created_at datetime [not null]
}

// ----------------------------------------------------
// DORMANT / PARTIAL ENTITIES (Schema defined, not active in business flows)
// ----------------------------------------------------

Table banners {
  id bigint [pk, increment]
  title varchar(255) [not null]
  subtitle varchar(255) [null]
  image_path varchar(255) [not null]
  button_text varchar(255) [null]
  button_url varchar(255) [null]
  starts_at datetime [null]
  ends_at datetime [null]
  sort_order integer [not null, default: 0]
  is_active boolean [not null, default: true]
  created_at datetime [null]
  updated_at datetime [null]
}

Table notifications {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  type varchar(255) [not null]
  title varchar(255) [not null]
  message text [not null]
  data text [null]
  read_at datetime [null]
  created_at datetime [null]
  updated_at datetime [null]
}

Table product_views {
  id bigint [pk, increment]
  user_id bigint [null, ref: > users.id]
  product_id bigint [not null, ref: > products.id]
  session_id varchar(255) [null]
  created_at datetime [not null]

  Indexes {
    created_at
  }
}

// ----------------------------------------------------
// SYSTEM / FRAMEWORK TABLES (Laravel Standard)
// ----------------------------------------------------

Table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255) [not null]
  created_at datetime [null]
}

Table sessions {
  id varchar(255) [pk]
  user_id bigint [null]
  ip_address varchar(45) [null]
  user_agent text [null]
  payload text [not null]
  last_activity integer [not null]

  Indexes {
    user_id
    last_activity
  }
}

Table cache {
  key varchar(255) [pk]
  value text [not null]
  expiration integer [not null]

  Indexes {
    expiration
  }
}

Table cache_locks {
  key varchar(255) [pk]
  owner varchar(255) [not null]
  expiration integer [not null]

  Indexes {
    expiration
  }
}

Table jobs {
  id bigint [pk, increment]
  queue varchar(255) [not null]
  payload text [not null]
  attempts integer [not null]
  reserved_at integer [null]
  available_at integer [not null]
  created_at integer [not null]

  Indexes {
    queue
  }
}

Table job_batches {
  id varchar(255) [pk]
  name varchar(255) [not null]
  total_jobs integer [not null]
  pending_jobs integer [not null]
  failed_jobs integer [not null]
  failed_job_ids text [not null]
  options text [null]
  cancelled_at integer [null]
  created_at integer [not null]
  finished_at integer [null]
}

Table failed_jobs {
  id bigint [pk, increment]
  uuid varchar(255) [unique, not null]
  connection text [not null]
  queue text [not null]
  payload text [not null]
  exception text [not null]
  failed_at datetime [not null]

  Indexes {
    uuid [unique]
  }
}
```

---

## 3. Data Dictionary (Kamus Data Aktual)

### 3.1 Entitas Pengguna & Akses

#### Tabel `users`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `name` | VARCHAR(255) | Tidak | - | Nama lengkap customer atau admin |
| `email` | VARCHAR(255) | Tidak | - | Alamat email unik (login username) |
| `email_verified_at` | DATETIME | Ya | NULL | Waktu verifikasi email |
| `password` | VARCHAR(255) | Tidak | - | Hash Bcrypt password |
| `phone` | VARCHAR(255) | Ya | NULL | Nomor kontak telepon / WhatsApp |
| `role` | VARCHAR(20) | Tidak | `'customer'` | Nilai: `'customer'`, `'admin'` |
| `google_id` | VARCHAR(255) | Ya | NULL | ID unik dari Google OAuth Socialite |
| `remember_token` | VARCHAR(100) | Ya | NULL | Token "remember me" session |
| `created_at` / `updated_at` | DATETIME | Ya | NULL | Timestamp Laravel Eloquent |

#### Tabel `addresses`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `user_id` | BIGINT UNSIGNED | Tidak | - | FK ke `users.id` (ON DELETE CASCADE) |
| `label` | VARCHAR(255) | Ya | NULL | Label alamat (mis. "Rumah", "Kantor") |
| `recipient_name` | VARCHAR(255) | Tidak | - | Nama lengkap penerima barang |
| `phone` | VARCHAR(255) | Tidak | - | Nomor HP penerima |
| `address_line` | TEXT | Tidak | - | Alamat jalan, RT/RW, nomor rumah |
| `village` | VARCHAR(255) | Ya | NULL | Kelurahan / Desa |
| `district` | VARCHAR(255) | Ya | NULL | Kecamatan |
| `city` | VARCHAR(255) | Tidak | - | Kota / Kabupaten |
| `province` | VARCHAR(255) | Tidak | - | Provinsi |
| `postal_code` | VARCHAR(20) | Tidak | - | Kode pos pengiriman |
| `notes` | VARCHAR(255) | Ya | NULL | Patokan pengiriman kurir |
| `is_default` | BOOLEAN | Tidak | `false` | Menandakan alamat pengiriman default |

---

### 3.2 Entitas Katalog & Inventaris

#### Tabel `categories`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `parent_id` | BIGINT UNSIGNED | Ya | NULL | Self-reference FK ke `categories.id` (ON DELETE SET NULL) |
| `name` | VARCHAR(255) | Tidak | - | Nama kategori (mis. "Pashmina", "Square") |
| `slug` | VARCHAR(255) | Tidak | - | URL slug unik |
| `description` | TEXT | Ya | NULL | Deskripsi kategori |
| `image` | VARCHAR(255) | Ya | NULL | Path gambar banner kategori di Supabase |
| `is_active` | BOOLEAN | Tidak | `true` | Status tampil di storefront |
| `deleted_at` | DATETIME | Ya | NULL | Soft delete |

#### Tabel `products`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `category_id` | BIGINT UNSIGNED | Tidak | - | FK ke `categories.id` (ON DELETE RESTRICT) |
| `name` | VARCHAR(255) | Tidak | - | Nama produk hijab |
| `slug` | VARCHAR(255) | Tidak | - | URL slug unik produk |
| `sku` | VARCHAR(255) | Tidak | - | Kode unik induk SKU produk |
| `description` | TEXT | Ya | NULL | Deskripsi panjang dan fitur hijab |
| `material` | VARCHAR(255) | Ya | NULL | Jenis bahan (mis. "Voal Premium", "Silk") |
| `care_instructions` | TEXT | Ya | NULL | Petunjuk pencucian dan perawatan |
| `base_price` | DECIMAL(12,2) | Tidak | - | Harga dasar produk |
| `compare_at_price` | DECIMAL(12,2) | Ya | NULL | Harga coret / harga referensi diskon |
| `is_featured` | BOOLEAN | Tidak | `false` | Ditampilkan pada section Featured |
| `is_best_seller` | BOOLEAN | Tidak | `false` | Ditampilkan pada section Best Seller |
| `is_active` | BOOLEAN | Tidak | `true` | Status publikasi produk |
| `rating_avg` | DECIMAL(3,2) | Tidak | `0.00` | Nilai agregat rating ulasan (denormalized) |
| `rating_count` | INTEGER | Tidak | `0` | Jumlah ulasan terverifikasi (denormalized) |
| `deleted_at` | DATETIME | Ya | NULL | Soft delete |

#### Tabel `product_variants`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `product_id` | BIGINT UNSIGNED | Tidak | - | FK ke `products.id` (ON DELETE CASCADE) |
| `name` | VARCHAR(255) | Tidak | - | Nama varian (mis. "Dusty Rose - Standard") |
| `sku` | VARCHAR(255) | Tidak | - | Kode SKU unik varian |
| `color_name` | VARCHAR(255) | Ya | NULL | Nama warna varian |
| `color_hex` | VARCHAR(255) | Ya | NULL | Kode heksadesimal warna (mis. `#D98FAF`) |
| `size` | VARCHAR(255) | Ya | NULL | Ukuran (mis. "115 x 115 cm", "180 x 75 cm") |
| `additional_price` | DECIMAL(12,2) | Tidak | `0.00` | Selisih harga tambahan di atas `base_price` |
| `stock_qty` | INTEGER | Tidak | `0` | Jumlah stok riil tersedia di gudang |
| `low_stock_threshold`| INTEGER | Tidak | `5` | Batas minimal peringatan stok menipis |
| `weight_gram` | INTEGER | Ya | NULL | Berat kotor per pcs dalam satuan gram |
| `is_active` | BOOLEAN | Tidak | `true` | Status keaktifan varian |

#### Tabel `product_images`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `product_id` | BIGINT UNSIGNED | Tidak | - | FK ke `products.id` (ON DELETE CASCADE) |
| `variant_id` | BIGINT UNSIGNED | Ya | NULL | FK opsional ke `product_variants.id` (ON DELETE SET NULL) |
| `image_path` | VARCHAR(255) | Tidak | - | Path objek pada Supabase Storage |
| `alt_text` | VARCHAR(255) | Ya | NULL | Alternatif teks untuk aksesibilitas & SEO |
| `sort_order` | INTEGER | Tidak | `0` | Urutan tampil di galeri |
| `is_primary` | BOOLEAN | Tidak | `false` | Menandakan foto sampul utama produk |

#### Tabel `inventory_movements`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `product_variant_id`| BIGINT UNSIGNED | Tidak | - | FK ke `product_variants.id` (ON DELETE CASCADE) |
| `type` | VARCHAR(20) | Tidak | - | Nilai: `'in'`, `'out'`, `'adjustment'`, `'return'` |
| `quantity` | INTEGER | Tidak | - | Jumlah unit mutasi stok |
| `reference_type` | VARCHAR(255) | Ya | NULL | Model referensi polimorfik (mis. `App\Models\Order`) |
| `reference_id` | BIGINT | Ya | NULL | ID dokumen referensi mutasi |
| `note` | VARCHAR(255) | Ya | NULL | Catatan deskripsi mutasi stok |
| `created_by` | BIGINT UNSIGNED | Ya | NULL | FK ke `users.id` pembuat mutasi (ON DELETE SET NULL) |
| `created_at` | DATETIME | Tidak | CURRENT_TIMESTAMP | Waktu pencatatan mutasi fisik |

---

### 3.3 Entitas Transaksi & Pesanan

#### Tabel `carts` & `cart_items`
- `carts.user_id` memiliki constraint UNIQUE (satu keranjang aktif per akun customer).
- `carts.session_id` memiliki constraint UNIQUE untuk tamu (*guest*).
- `cart_items` memiliki index UNIQUE majemuk pada `(cart_id, product_variant_id)` agar varian yang sama digabungkan dengan menambah kuantitas, bukan membuat baris baru.
- `cart_items.unit_price` dihitung ulang di server saat item dimasukkan.

#### Tabel `orders`
| Kolom | Tipe Data | Nullable | Default | Keterangan & Constraint |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | Tidak | Auto-inc | Primary key |
| `order_number` | VARCHAR(255) | Tidak | - | Nomor pesanan unik (mis. `ORD-20261027-ABCDE`) |
| `user_id` | BIGINT UNSIGNED | Tidak | - | FK ke `users.id` (ON DELETE RESTRICT) |
| `status` | VARCHAR(20) | Tidak | `'pending'` | State machine: `pending`, `confirmed`, `processing`, `packed`, `shipped`, `delivered`, `completed`, `cancelled` |
| `payment_status` | VARCHAR(20) | Tidak | `'pending'` | Status bayar: `pending`, `paid`, `failed`, `expired`, `refunded` |
| `subtotal` | DECIMAL(12,2) | Tidak | - | Total nilai item sebelum diskon dan ongkir |
| `discount_amount` | DECIMAL(12,2) | Tidak | `0.00` | Nilai potongan voucher |
| `shipping_cost` | DECIMAL(12,2) | Tidak | `0.00` | Biaya ongkir ekspedisi yang dipilih |
| `grand_total` | DECIMAL(12,2) | Tidak | - | Total akhir tagihan yang wajib dibayar |
| `voucher_code` | VARCHAR(255) | Ya | NULL | Snapshot kode voucher yang diaplikasikan |
| `voucher_discount` | DECIMAL(12,2) | Tidak | `0.00` | Nilai voucher yang dipotong |
| `shipping_recipient_name`| VARCHAR(255) | Tidak | - | Snapshot nama penerima |
| `shipping_phone` | VARCHAR(255) | Tidak | - | Snapshot telepon penerima |
| `shipping_address` | TEXT | Tidak | - | Snapshot alamat lengkap |
| `shipping_city` | VARCHAR(255) | Tidak | - | Snapshot kota |
| `shipping_province`| VARCHAR(255) | Tidak | - | Snapshot provinsi |
| `shipping_postal_code`| VARCHAR(20) | Tidak | - | Snapshot kode pos |
| `customer_note` | TEXT | Ya | NULL | Catatan khusus dari pembeli |
| `placed_at` | DATETIME | Ya | NULL | Waktu pesanan berhasil dibuat |
| `delivered_at` | DATETIME | Ya | NULL | Waktu pesanan ditandai terkirim |
| `review_reminded_at`| DATETIME | Ya | NULL | Waktu pengiriman email reminder review |
| `checkout_token` | VARCHAR(255) | Ya | NULL | Token idempotensi UUID sesi checkout |
| `checkout_fingerprint`| VARCHAR(255) | Ya | NULL | Hash SHA256 parameter request checkout |
| `checkout_cart_fingerprint`| VARCHAR(255)| Ya | NULL | Hash identitas baris keranjang belanja |
| `checkout_source` | VARCHAR(50) | Ya | NULL | Nilai: `'cart'` atau `'buy_now'` |

#### Tabel `order_items`
Snapshot baris item pesanan: `product_id`, `product_variant_id`, `product_name`, `variant_name`, `sku`, `unit_price`, `quantity`, `subtotal`. Menjamin kestabilan faktur dan laporan audit keuangan.

#### Tabel `order_status_histories`
Pencatatan riwayat transisi status: `order_id` (FK), `from_status`, `to_status`, `reason`, `actor_id` (FK ke `users.id`), `created_at`.

#### Tabel `stock_reservations`
Penyangga stok checkout: `order_item_id` (FK nullable), `product_variant_id` (FK), `quantity`, `status` (`pending`, `reserved`, `released`, `cancelled`), `expires_at`. Stok direservasi saat checkout dibuat dan dilepas otomatis jika pembayaran kadaluwarsa.

#### Tabel `payments`
Pencatatan sesi pembayaran Midtrans: `order_id` (FK), `provider` (`midtrans`), `payment_method`, `transaction_reference`, `amount`, `status` (`pending`, `paid`, `failed`, `expired`, `refunded`), `paid_at`, `metadata` (JSON menyimpan snap token, token expiry, dan webhook payload).

#### Tabel `payment_initialization_attempts`
Tabel kunci sewa (*lease*) database untuk memastikan pembuatan Snap Midtrans bersifat atomik dan idempotent di lingkungan Vercel serverless. Memuat `idempotency_key` (UNIQUE), `request_fingerprint`, `status` (`processing`, `completed`, `retryable`, `ambiguous`), `lease_expires_at`, dan `payment_id`.

#### Tabel `shipments`
Data pengiriman ekspedisi: `order_id` (UNIQUE, 1:1 dengan order), `courier`, `courier_code`, `service`, `tracking_number` (nomor resi), `shipping_cost`, `destination_id` (ID subdistrict RajaOngkir), `weight_gram`, `estimated_delivery` (estimasi hari), `status` (`pending`, `shipped`, `delivered`), `shipped_at`, `delivered_at`.

#### Tabel `reviews`
Ulasan pembeli: `product_id` (FK), `user_id` (FK), `order_item_id` (FK UNIQUE, 1 ulasan per barang yang dibeli), `rating` (1–5), `title`, `comment`, `photo_path`, `is_verified_purchase`, `is_published`, `deleted_at`.

#### Tabel `vouchers` & `voucher_usages`
Pengaturan kupon promosi dan pencatatan riwayat pemakaian per pesanan dan user.

---

## 4. Constraint, Index, dan Referential Integrity

### 4.1 Referential Integrity (Foreign Key Constraints)
| Hubungan Relasi | Foreign Key | Cascade Behavior | Alasan Desain |
|---|---|---|---|
| `categories` -> `categories` | `parent_id` | ON DELETE SET NULL | Jika kategori induk dihapus, subkategori tetap ada. |
| `products` -> `categories` | `category_id` | ON DELETE RESTRICT | Kategori yang memiliki produk tidak boleh dihapus mendadak. |
| `product_variants` -> `products` | `product_id` | ON DELETE CASCADE | Varian melekat utuh pada produk induknya. |
| `product_images` -> `products` | `product_id` | ON DELETE CASCADE | Foto produk dihapus saat produk induk dihapus. |
| `product_images` -> `product_variants`| `variant_id`| ON DELETE SET NULL | Menghapus varian tidak menghapus galeri umum. |
| `inventory_movements` -> `product_variants`| `product_variant_id`| ON DELETE CASCADE | Audit fisik terkait varian. |
| `cart_items` -> `carts` | `cart_id` | ON DELETE CASCADE | Menghapus cart membersihkan item di dalamnya. |
| `cart_items` -> `product_variants` | `product_variant_id` | ON DELETE RESTRICT | Varian yang ada di cart pembeli dilindungi dari penghapusan keras. |
| `orders` -> `users` | `user_id` | ON DELETE RESTRICT | Akun pelanggan yang memiliki riwayat order tidak boleh dihapus sembarangan. |
| `order_items` -> `orders` | `order_id` | ON DELETE CASCADE | Item pesanan merupakan anak transaksi dari pesanan. |
| `order_items` -> `products` / `product_variants` | `product_id` / `variant_id` | ON DELETE RESTRICT | Melindungi integritas data histori hukum pesanan. |
| `order_status_histories` -> `orders` | `order_id` | ON DELETE CASCADE | Riwayat status pesanan terhapus jika pesanan dihapus. |
| `stock_reservations` -> `product_variants` | `product_variant_id` | ON DELETE CASCADE | Reservasi stok mengikuti varian barang. |
| `payments` -> `orders` | `order_id` | ON DELETE CASCADE | Pembayaran melekat pada pesanan terkait. |
| `shipments` -> `orders` | `order_id` | ON DELETE CASCADE | Pengiriman melekat pada pesanan (relasi 1:1). |
| `reviews` -> `order_items` | `order_item_id` | ON DELETE RESTRICT | Menjamin relasi ulasan pembelian terverifikasi. |
| `voucher_usages` -> `vouchers` | `voucher_id` | ON DELETE RESTRICT | Voucher yang pernah dipakai tidak boleh dihapus keras dari audit. |

### 4.2 Index Strategis Performa & Integritas
1. **Pencegahan Duplikasi & Idempotensi:**
   - `users.email` (UNIQUE) & `users.google_id` (UNIQUE).
   - `orders.order_number` (UNIQUE).
   - `orders(user_id, checkout_token)` (UNIQUE) — Mencegah klik ganda checkout membuat 2 pesanan berbeda.
   - `payment_initialization_attempts.idempotency_key` (UNIQUE) — Menjamin Midtrans Snap hanya di-request satu kali per sewa.
   - `payment_initialization_attempts(order_id, attempt_number)` (UNIQUE).
   - `carts.user_id` (UNIQUE) & `carts.session_id` (UNIQUE).
   - `cart_items(cart_id, product_variant_id)` (UNIQUE).
   - `wishlists(user_id, product_id)` (UNIQUE).
   - `reviews.order_item_id` (UNIQUE) — 1 pesanan item hanya dapat diulas 1 kali.
   - `shipments.order_id` (UNIQUE) — Relasi ketat satu resi/pengiriman per pesanan.
2. **Optimasi Filter & Sorting Query Publik:**
   - `products.is_active`, `products.is_featured`, `products.is_best_seller`.
   - `product_variants.stock_qty` (Pencarian barang tersedia & ambang stok).
   - `vouchers.is_active`, `vouchers.starts_at`, `vouchers.expires_at`.
3. **Optimasi Dashboard & Antrean Operasional Admin:**
   - `orders.status`, `orders.payment_status`, `orders.created_at`.
   - `order_status_histories(order_id, id)`.
   - `stock_reservations.status`.

---

## 5. Perbandingan ERD Lama vs Implementasi Aktual

Audit perbandingan antara berkas rancangan awal (`erd_old.tmp`) dan migrasi aktual di database:

| Entitas / Fitur | Status Rancangan Lama | Implementasi Aktual pada Source Code | Implikasi & Analisis Teknis |
|---|---|---|---|
| `stock_reservations` | Tidak ada | **Tabel Diterapkan** (Migration `2026_10_12_000001`) | **Peningkatan Kritis:** Mencegah *overselling* saat checkout dengan menahan kuantitas selama 15 menit sebelum pembayaran diverifikasi. |
| `order_status_histories` | Tidak ada | **Tabel Diterapkan** (Migration `2026_10_25_000001`) | **Audit Trail Lengkap:** Mencatat riwayat perpindahan status pesanan beserta alasan pembatalan dan identitas aktor. |
| `payment_initialization_attempts` | Tidak ada | **Tabel Diterapkan** (Migration `2026_10_25_000003`) | **Ketahanan Serverless Vercel:** Menyediakan mekanisme *lease lock* tingkat baris basis data untuk menjamin idempotensi Snap Midtrans saat `CACHE_STORE=array`. |
| Kolom Idempotensi `orders` | Hanya kolom standar | Kolom ditambahkan: `checkout_token`, `checkout_fingerprint`, `checkout_cart_fingerprint`, `checkout_source` | Menghindari pembuatan pesanan berulang akibat koneksi terputus atau tombol *submit* ditekan ganda. |
| Integrasi RajaOngkir pada `shipments` | Kolom generik | Kolom ditambahkan: `destination_id`, `courier_code`, `weight_gram`, `estimated_delivery` | Menyimpan snapshot data ongkir RajaOngkir/Komerce V2 secara akurat per transaksi. |
| Rating Denormalized pada `products` | Tidak dirancang | Kolom ditambahkan: `rating_avg` dan `rating_count` | Mempercepat *query sorting* rating tertinggi pada katalog tanpa kalkulasi `AVG()` berulang. |
| Google OAuth pada `users` | Hanya email/password | Kolom ditambahkan: `google_id` (UNIQUE) | Mendukung autentikasi *single sign-on* via Socialite. |
| Soft Delete pada `reviews` | Tidak ada soft delete | Kolom `deleted_at` diterapkan | Admin dapat menghapus ulasan yang melanggar tanpa merusak referensi data `order_items`. |
| Unique Cart per User | `user_id` tidak unik | `user_id` memiliki index UNIQUE | Mencegah anomali多 cart aktif untuk satu customer. |
| `banners`, `notifications`, `product_views` | Dirancang sebagai fitur inti | Tabel & Model ada, **tetapi tidak digunakan dalam runtime controller** | Bersifat parsial/terencana (*dormant*). Tidak ada form admin atau penampil dinamis di storefront saat ini. |

---

## 6. Rekomendasi Peningkatan Schema Masa Depan

Berdasarkan temuan audit kode dan karakteristik infrastruktur Vercel Serverless:

1. **Aktivasi atau Depresiasi Entitas Parsial:**
   - Tabel `banners` saat ini tidak terpakai karena banner beranda di-hardcode pada Blade. Jika tidak akan dibuatkan Admin CRUD, pertimbangkan untuk menghapus migrasi ini atau segera sediakan controller pengelolanya.
   - Tabel `product_views` tidak pernah diisi oleh `CatalogController@show`. Aktifkan pencatatan jika ingin membangun analitik produk terpopuler, atau tunda tabel ini agar tidak membingungkan schema.
2. **Pembersihan Log Historis (Data Retention Policy):**
   - Tabel `payment_initialization_attempts` dan `order_status_histories` akan tumbuh cepat. Buat scheduled job untuk mengarsipkan atau menghapus percobaan pembayaran *completed* yang lebih tua dari 30 hari.
3. **Dukungan Foto Ulasan Pelanggan:**
   - Kolom `reviews.photo_path` sudah tersedia, tetapi antarmuka unggah foto ulasan pada `ReviewController` belum diaktifkan. Perlu ditambahkan integrasi unggah gambar ke Supabase Storage pada form ulasan.
4. **Indeks Pencarian Katalog Tingkat Lanjut:**
   - Saat ini pencarian produk pada `CatalogController` menggunakan klausa SQL `LIKE %term%`. Untuk basis data produksi Clever Cloud MySQL dengan ribuan item, direkomendasikan menambahkan `FULLTEXT INDEX` pada kolom `(name, description, material)`.
