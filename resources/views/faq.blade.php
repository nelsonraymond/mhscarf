<x-layouts.app>
    @section('title', 'FAQ — MH Scarf Store')

    @php
        $waNumber = '6281234567890'; // samakan dengan nomor WhatsApp di footer

        $groups = [
            [
                'id' => 'pemesanan',
                'title' => 'Pemesanan & Akun',
                'items' => [
                    ['q' => 'Apakah saya harus punya akun untuk berbelanja?',
                     'a' => 'Ya. Untuk menyimpan keranjang, wishlist, dan menyelesaikan checkout, Anda perlu masuk ke akun. Pendaftaran cepat: cukup klik "Daftar dengan Google", atau daftar memakai email.'],
                    ['q' => 'Bagaimana cara memesan?',
                     'a' => 'Pilih produk dan varian, lalu klik "Tambah ke Keranjang" atau "Beli Sekarang". Di halaman checkout, pilih alamat dan metode pengiriman, lalu klik "Bayar" dan selesaikan pembayaran pada jendela Midtrans.'],
                    ['q' => 'Apakah stok produk dijamin setelah saya memesan?',
                     'a' => 'Ya. Stok dikunci otomatis saat pesanan dibuat, selama batas waktu pembayaran masih berlaku. Jika pembayaran tidak diselesaikan tepat waktu, pesanan dibatalkan otomatis dan stok dikembalikan.'],
                    ['q' => 'Bisakah saya mengubah atau membatalkan pesanan?',
                     'a' => 'Detail pesanan tidak dapat diubah setelah dibuat. Selama statusnya masih memungkinkan, Anda dapat membatalkan pesanan dari halaman "Pesanan Saya" lalu membuat pesanan baru. Jika tombol batal tidak tersedia, hubungi kami lewat WhatsApp secepatnya.'],
                ],
            ],
            [
                'id' => 'pembayaran',
                'title' => 'Pembayaran',
                'items' => [
                    ['q' => 'Metode pembayaran apa saja yang tersedia?',
                     'a' => 'Pembayaran diproses oleh Midtrans. Pilihan yang tersedia, seperti virtual account bank, e-wallet, QRIS, dan kartu, akan tampil di jendela pembayaran setelah Anda klik "Bayar".'],
                    ['q' => 'Berapa lama batas waktu pembayaran?',
                     'a' => 'Batas waktunya singkat, umumnya sekitar 15 menit sejak pesanan dibuat. Setelah itu pesanan kedaluwarsa dan stok dilepas. Jika terlewat, silakan buat pesanan baru.'],
                    ['q' => 'Saya sudah membayar, tetapi status pesanan belum berubah.',
                     'a' => 'Tunggu beberapa saat lalu buka kembali halaman "Pesanan Saya" atau halaman pembayaran, karena status diperbarui otomatis. Jika setelah beberapa menit belum berubah, hubungi kami dengan menyertakan nomor pesanan dan bukti pembayaran.'],
                    ['q' => 'Jendela pembayaran tertutup sebelum saya selesai membayar.',
                     'a' => 'Tidak masalah. Buka "Pesanan Saya", pilih pesanan tersebut, lalu lanjutkan pembayaran dari halaman pembayarannya selama batas waktunya belum habis.'],
                ],
            ],
            [
                'id' => 'pengiriman',
                'title' => 'Pengiriman',
                'items' => [
                    ['q' => 'Opsi pengiriman apa yang tersedia?',
                     'a' => 'Tersedia dua pilihan: MH Scarf Express Delivery Reguler (estimasi 2–4 hari kerja) dan Express (estimasi 1–2 hari kerja). Tarif ditampilkan di halaman checkout sebelum Anda membayar.'],
                    ['q' => 'Kapan pesanan saya dikirim?',
                     'a' => 'Pesanan yang sudah dibayar dikirim setiap hari kerja, Senin sampai Sabtu. Estimasi lama pengiriman dihitung sejak paket diserahkan ke kurir.'],
                    ['q' => 'Bagaimana cara melacak pesanan saya?',
                     'a' => 'Masuk ke akun, buka "Pesanan Saya", lalu pilih pesanan Anda untuk melihat statusnya. Nomor resi akan muncul di sana setelah paket dikirim.'],
                    ['q' => 'Apakah pengiriman ke seluruh Indonesia?',
                     'a' => 'Ya, kami melayani pengiriman ke seluruh wilayah Indonesia.'],
                ],
            ],
            [
                'id' => 'pengembalian',
                'title' => 'Pengembalian & Penukaran',
                'items' => [
                    ['q' => 'Berapa lama batas waktu pengembalian?',
                     'a' => 'Anda dapat mengajukan pengembalian dalam 7 hari setelah barang diterima.'],
                    ['q' => 'Kondisi apa yang bisa diretur?',
                     'a' => 'Barang yang diterima rusak, cacat produksi, atau tidak sesuai pesanan. Produk harus belum dipakai atau dicuci, dan label masih terpasang. Sertakan foto atau video unboxing sebagai bukti.'],
                    ['q' => 'Bagaimana cara mengajukan pengembalian?',
                     'a' => 'Hubungi kami lewat WhatsApp dengan nomor pesanan, foto atau video kondisi barang, dan alasan pengembalian. Tim kami akan memandu langkah selanjutnya.'],
                ],
            ],
            [
                'id' => 'produk',
                'title' => 'Produk & Perawatan',
                'items' => [
                    ['q' => 'Apakah warna produk sama persis dengan foto?',
                     'a' => 'Kami berusaha menampilkan warna seakurat mungkin, tetapi tampilan bisa sedikit berbeda karena pencahayaan dan pengaturan layar perangkat Anda.'],
                    ['q' => 'Bagaimana cara merawat hijab agar awet?',
                     'a' => 'Cuci lembut dengan tangan, hindari menjemur langsung di bawah terik matahari, dan setrika dengan suhu rendah. Petunjuk perawatan tiap produk tertera di halaman produknya.'],
                    ['q' => 'Apa arti "Grade" pada bahan produk?',
                     'a' => 'Grade menunjukkan kelas kualitas bahan yang digunakan. Keterangan bahan dan perawatan untuk setiap produk dapat dilihat di halaman detail produk.'],
                ],
            ],
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($groups)->flatMap(fn ($g) => $g['items'])->map(fn ($i) => [
                '@type' => 'Question',
                'name' => $i['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['a']],
            ])->values()->all(),
        ];
    @endphp

    <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="text-xs text-muted mb-6">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep rounded transition">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink font-medium">FAQ</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-[11px] uppercase tracking-[0.2em] text-pink-mauve font-semibold mb-1">Pusat Bantuan</p>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink">Pertanyaan yang Sering Diajukan</h1>
            <p class="text-sm text-muted mt-1.5">Temukan jawaban seputar pemesanan, pembayaran, pengiriman, dan pengembalian.</p>
        </div>

        {{-- Category chips --}}
        <nav aria-label="Kategori FAQ" class="flex flex-wrap gap-2 mb-8">
            @foreach ($groups as $g)
                <a href="#{{ $g['id'] }}"
                    class="inline-flex items-center h-9 px-4 rounded-full border border-line bg-white text-xs font-medium text-ink hover:border-pink-deep hover:text-pink-deep transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">
                    {{ $g['title'] }}
                </a>
            @endforeach
        </nav>

        {{-- Groups --}}
        <div class="space-y-8">
            @foreach ($groups as $g)
                <section id="{{ $g['id'] }}" class="scroll-mt-28" aria-labelledby="{{ $g['id'] }}-h">
                    <h2 id="{{ $g['id'] }}-h" class="font-display text-lg font-bold text-ink mb-3">{{ $g['title'] }}</h2>

                    <div class="bg-white rounded-2xl border border-line shadow-card divide-y divide-line overflow-hidden">
                        @foreach ($g['items'] as $item)
                            <details class="group">
                                <summary class="flex items-center justify-between gap-4 px-5 sm:px-6 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden hover:bg-cream/60 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-pink-deep">
                                    <span class="text-sm font-medium text-ink">{{ $item['q'] }}</span>
                                    <svg class="w-4 h-4 shrink-0 text-pink-deep transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </summary>
                                <div class="px-5 sm:px-6 pb-5 -mt-1 text-sm text-muted leading-relaxed">
                                    {{ $item['a'] }}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Contact CTA --}}
        <section class="mt-12 rounded-2xl border border-line bg-pink-soft/30 p-6 sm:p-8 text-center" aria-labelledby="faq-contact-h">
            <h2 id="faq-contact-h" class="font-display text-lg font-bold text-ink">Belum menemukan jawabannya?</h2>
            <p class="text-sm text-muted mt-1.5 mb-5">Tim kami siap membantu Anda lewat WhatsApp.</p>
            <x-primary-button :href="'https://wa.me/' . $waNumber" target="_blank" rel="noopener noreferrer">
                Hubungi Kami via WhatsApp
            </x-primary-button>
        </section>
    </div>
</x-layouts.app>