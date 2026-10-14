<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use App\Services\StockReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredOrders extends Command
{
    protected $signature = 'orders:release-expired';

    protected $description = 'Batalkan pesanan yang melewati batas waktu pembayaran dan kembalikan stok yang ditahan';

    public function handle(PaymentService $payments, StockReservationService $reservations): int
    {
        $stats = $payments->releaseOverdueOrders();
        $orphans = $reservations->releaseExpiredOrphans();

        $this->info(sprintf(
            'Dibatalkan: %d, dikonfirmasi lunas: %d, dilewati: %d, reservasi yatim dilepas: %d',
            $stats['expired'],
            $stats['confirmed'],
            $stats['skipped'],
            $orphans,
        ));

        return self::SUCCESS;
    }
}