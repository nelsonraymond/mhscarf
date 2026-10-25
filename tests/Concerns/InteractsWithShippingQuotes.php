<?php

namespace Tests\Concerns;

use App\Models\Address;
use App\Services\ShippingService;
use Mockery;

trait InteractsWithShippingQuotes
{
    protected function fakeShippingQuote(int $cost = 16000, int $weight = 150): string
    {
        $quote = [
            'destination_id' => 17620,
            'courier' => 'jne',
            'courier_name' => 'JNE',
            'service' => 'REG',
            'description' => 'Layanan Reguler',
            'cost' => $cost,
            'weight_gram' => $weight,
            'etd' => '2 day',
        ];

        $shipping = Mockery::mock(ShippingService::class);
        $shipping->shouldReceive('revalidateQuote')->andReturn($quote);
        $shipping->shouldReceive('resolveQuote')->andReturn($quote);
        $this->app->instance(ShippingService::class, $shipping);

        return 'test-shipping-quote';
    }

    /** @return array<string, int|string> */
    protected function internalShippingQuote(int $weight = 150, int $cost = 16000): array
    {
        return [
            'destination_id' => 17620,
            'courier' => 'jne',
            'courier_name' => 'JNE',
            'service' => 'REG',
            'description' => 'Layanan Reguler',
            'cost' => $cost,
            'weight_gram' => $weight,
            'etd' => '2 day',
        ];
    }
}
