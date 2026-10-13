<?php

namespace App\Services;

use InvalidArgumentException;

class ShippingService
{
    /**
     * Get list of available shipping methods.
     *
     * @return array<string, array{code: string, name: string, courier: string, service: string, cost: float, estimated_days: string, description: string}>
     */
    public function getAvailableMethods(): array
    {
        return [
            'regular' => [
                'code' => 'regular',
                'name' => 'Reguler',
                'courier' => 'MH Scarf Express Delivery',
                'service' => 'Reguler',
                'cost' => 15000.0,
                'estimated_days' => '2-4 Hari Kerja',
                'description' => 'Pengiriman standar ke seluruh wilayah Indonesia (estimasi 2-4 hari kerja)',
            ],
            'express' => [
                'code' => 'express',
                'name' => 'Express',
                'courier' => 'MH Scarf Express Delivery',
                'service' => 'Express',
                'cost' => 30000.0,
                'estimated_days' => '1-2 Hari Kerja',
                'description' => 'Pengiriman kilat prioritas tinggi (estimasi 1-2 hari kerja)',
            ],
        ];
    }

    /**
     * Get specific shipping method by code.
     *
     * @return array{code: string, name: string, courier: string, service: string, cost: float, estimated_days: string, description: string}
     */
    public function getMethod(string $code): array
    {
        $methods = $this->getAvailableMethods();

        if (! isset($methods[$code])) {
            throw new InvalidArgumentException("Metode pengiriman tidak valid: {$code}");
        }

        return $methods[$code];
    }

    /**
     * Calculate shipping cost based on method code.
     */
    public function calculateCost(string $code): float
    {
        return $this->getMethod($code)['cost'];
    }
}
