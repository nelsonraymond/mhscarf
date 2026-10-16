<?php

namespace Tests\Feature;

use App\Models\Voucher;
use App\Services\VoucherService;
use Tests\TestCase;

class VoucherCalculationTest extends TestCase
{
    private function service(): VoucherService
    {
        return app(VoucherService::class);
    }

    public function test_percentage_discount_is_capped_by_max_discount(): void
    {
        $v = new Voucher(['type' => 'percentage', 'value' => 10, 'max_discount_amount' => 20000]);

        $this->assertSame(20000.0, $this->service()->calculate($v, 500000, 15000));
        $this->assertSame(9000.0, $this->service()->calculate($v, 90000, 15000));
    }

    public function test_fixed_discount_never_exceeds_subtotal(): void
    {
        $v = new Voucher(['type' => 'fixed', 'value' => 50000]);

        $this->assertSame(30000.0, $this->service()->calculate($v, 30000, 15000));
    }

    public function test_free_shipping_covers_shipping_cost_only(): void
    {
        $v = new Voucher(['type' => 'free_shipping', 'value' => 0]);

        $this->assertSame(30000.0, $this->service()->calculate($v, 90000, 30000));
    }
}