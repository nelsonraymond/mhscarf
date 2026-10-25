@extends('emails.transactional.layout')

@section('title', 'Pesanan sedang dikirim - MH SCARF')

@section('content')
    <h1 class="heading">Pesanan Anda sedang dikirim</h1>
    <p>Halo {{ $order->user->name }}, pesanan <strong>{{ $order->order_number }}</strong> telah diserahkan kepada kurir.</p>
    <table class="details" role="presentation">
        <tbody>
            <tr><th>Kurir</th><td>{{ $shipment->courier }}</td></tr>
            <tr><th>Layanan</th><td>{{ $shipment->service ?: '—' }}</td></tr>
            <tr><th>Nomor resi</th><td>{{ $shipment->tracking_number ?: 'Belum tersedia' }}</td></tr>
        </tbody>
    </table>
    @if (! $shipment->tracking_number)
        <p class="muted">Nomor resi akan ditampilkan pada detail pesanan setelah tersedia.</p>
    @endif
    <a class="button" href="{{ route('orders.show', $order) }}">Lihat detail pesanan</a>
@endsection
