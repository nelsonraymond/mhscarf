@extends('emails.transactional.layout')

@section('title', 'Pesanan berhasil dibuat - MH SCARF')

@section('content')
    <h1 class="heading">Pesanan berhasil dibuat</h1>
    <p>Halo {{ $order->user->name }}, terima kasih telah berbelanja di MH SCARF. Pesanan Anda sedang menunggu pembayaran.</p>
    <p><strong>Nomor pesanan:</strong> {{ $order->order_number }}</p>

    <table class="details" role="presentation">
        <thead><tr><th>Produk</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}<br><span class="muted">{{ $item->variant_name }}</span></td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="details" role="presentation">
        <tbody>
            <tr><td>Subtotal</td><td style="text-align:right">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td></tr>
            @if ((float) $order->discount_amount > 0)
                <tr><td>Diskon</td><td style="text-align:right">-Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</td></tr>
            @endif
            <tr><td>Ongkos kirim</td><td style="text-align:right">Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</td></tr>
            <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong></td></tr>
        </tbody>
    </table>

    <p><strong>Alamat pengiriman</strong><br>{{ $order->shipping_recipient_name }} · {{ $order->shipping_phone }}<br>{{ $order->shipping_address }}</p>
    <p>Metode pembayaran tersedia di halaman pembayaran. Pesanan akan diproses setelah pembayaran terverifikasi oleh sistem.</p>
    <a class="button" href="{{ route('payments.show', $order) }}">Lanjutkan pembayaran</a>
    <p class="muted">Total tagihan: Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</p>
@endsection
