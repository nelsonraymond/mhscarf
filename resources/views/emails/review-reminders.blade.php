<x-mail::message>
# Halo, {{ $order->user->name }}

Pesanan **#{{ $order->order_number }}** sudah tiba. Kami ingin tahu pendapat Anda. Ulasan Anda membantu pembeli lain memilih hijab yang tepat.

@foreach ($items as $item)
- {{ $item->product_name }} ({{ $item->variant_name }})
@endforeach

<x-mail::button :url="route('orders.show', $order)">
Beri Ulasan
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>