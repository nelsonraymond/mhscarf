<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    // Lama stok ditahan dan batas maksimal pembayaran (menit). Snap dibuat kedaluwarsa
    // sedikit lebih cepat dari ini supaya Midtrans selalu expire lebih dulu daripada reservasi lokal.
    'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 15),

    // Toleransi (menit) setelah reservasi habis sebelum sweeper menyentuh pesanan,
    // memberi waktu bagi webhook "expire" dari Midtrans untuk datang lebih dulu.
    'expiry_grace_minutes' => (int) env('MIDTRANS_EXPIRY_GRACE_MINUTES', 3),
];