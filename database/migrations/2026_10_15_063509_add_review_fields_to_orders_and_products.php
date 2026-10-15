<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->timestamp('delivered_at')->nullable()->after('placed_at');
        $table->timestamp('review_reminded_at')->nullable()->after('delivered_at');
    });

    Schema::table('products', function (Blueprint $table) {
        $table->decimal('rating_avg', 3, 2)->default(0);
        $table->unsignedInteger('rating_count')->default(0);
    });

    // Pesanan lama: isi delivered_at, dan tandai sudah diingatkan supaya tidak ada email massal
    DB::table('orders')
        ->whereIn('status', ['delivered', 'completed'])
        ->update(['delivered_at' => DB::raw('updated_at'), 'review_reminded_at' => now()]);
}

public function down(): void
{
    Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['delivered_at', 'review_reminded_at']));
    Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['rating_avg', 'rating_count']));
}
};
