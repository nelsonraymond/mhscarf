<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->char('checkout_fingerprint', 64)->nullable()->after('checkout_token');
            $table->char('checkout_cart_fingerprint', 64)->nullable()->after('checkout_fingerprint');
            $table->string('checkout_source', 20)->nullable()->after('checkout_cart_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['checkout_source', 'checkout_cart_fingerprint', 'checkout_fingerprint']);
        });
    }
};
