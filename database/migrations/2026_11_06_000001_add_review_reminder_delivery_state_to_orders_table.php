<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('review_reminder_claimed_at')->nullable()->index();
            $table->uuid('review_reminder_claim_token')->nullable();
            $table->timestamp('review_reminder_started_at')->nullable();
            $table->json('review_reminder_item_ids')->nullable();
            $table->json('review_reminder_payload')->nullable();
            $table->timestamp('review_reminder_abandoned_at')->nullable();
            $table->timestamp('review_reminder_skipped_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['review_reminder_claimed_at']);
            $table->dropColumn([
                'review_reminder_claimed_at',
                'review_reminder_claim_token',
                'review_reminder_started_at',
                'review_reminder_item_ids',
                'review_reminder_payload',
                'review_reminder_abandoned_at',
                'review_reminder_skipped_at',
            ]);
        });
    }
};
