@extends('emails.transactional.layout')

@section('title', 'Pembayaran berhasil - MH SCARF')

@section('content')
    <h1 class="heading">Pembayaran berhasil diterima</h1>
    <p>Halo {{ $order->user->name }}, pembayaran untuk pesanan Anda telah diverifikasi oleh sistem MH SCARF.</p>
    <table class="details" role="presentation">
        <tbody>
            <tr><th>Nomor pesanan</th><td>{{ $order->order_number }}</td></tr>
            <tr><th>Nominal diterima</th><td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td></tr>
            <tr><th>Metode pembayaran</th><td>{{ $payment->payment_method ?: 'Midtrans' }}</td></tr>
            <tr><th>Referensi transaksi</th><td>{{ $payment->transaction_reference ?: '—' }}</td></tr>
        </tbody>
    </table>
    @if ($order->status === 'cancelled')
        <p>Pembayaran diterima setelah pesanan dibatalkan. Tim kami akan menindaklanjuti pengembalian dana.</p>
    @else
        <p>Pesanan Anda akan segera diproses. Kami akan mengirimkan kabar lagi saat pesanan diserahkan kepada kurir.</p>
    @endif
    <a class="button" href="{{ route('orders.show', $order) }}">Lihat detail pesanan</a>
@endsection
