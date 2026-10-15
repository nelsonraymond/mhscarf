<?php

namespace App\Console\Commands;

use App\Mail\ReviewReminderMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReviewReminders extends Command
{
    protected $signature = 'reviews:send-reminders';

    protected $description = 'Kirim pengingat ulasan 24 jam setelah pesanan tiba';

    public function handle(): int
    {
        $sent = 0;

        Order::query()
            ->whereIn('status', ['delivered', 'completed'])
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDay())
            ->whereNull('review_reminded_at')
            ->whereNotNull('user_id')
            ->with(['user', 'items.review'])
            ->chunkById(100, function ($orders) use (&$sent) {
                foreach ($orders as $order) {
                    $unreviewed = $order->items->filter(fn ($i) => $i->review === null);

                    if ($unreviewed->isNotEmpty() && $order->user?->email) {
                        try {
                            Mail::to($order->user->email)->send(new ReviewReminderMail($order, $unreviewed));
                            $sent++;
                        } catch (Throwable $e) {
                            // Jangan ditandai, akan dicoba lagi pada putaran berikutnya
                            Log::warning('Pengingat ulasan gagal', ['order' => $order->order_number, 'error' => $e->getMessage()]);

                            continue;
                        }
                    }

                    $order->update(['review_reminded_at' => now()]);
                }
            });

        $this->info("Pengingat terkirim: {$sent}");

        return self::SUCCESS;
    }
}