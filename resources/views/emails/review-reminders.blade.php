@extends('emails.transactional.layout')

@section('title', 'Pengingat Ulasan — MH SCARF')

@section('content')
    <h1 class="heading">Halo,</h1>
    <p>Pesanan <strong>#{{ $order->order_number }}</strong> sudah tiba. Kami ingin tahu pendapat Anda. Ulasan Anda membantu pembeli lain memilih hijab yang tepat.</p>

    <table class="details" role="presentation">
        <thead>
            <tr><th>Produk</th><th>Ulasan</th></tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->product_name }} ({{ $item->variant_name }})</td>
                    <td><a href="{{ route('reviews.create', $item) }}">Tulis ulasan</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Terima kasih telah berbelanja di MH SCARF.</p>
@endsection