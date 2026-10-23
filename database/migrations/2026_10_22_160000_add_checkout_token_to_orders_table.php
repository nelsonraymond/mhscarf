<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('checkout_token', 64)->nullable()->after('user_id');
            $table->unique(['user_id', 'checkout_token']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'checkout_token']);
            $table->dropColumn('checkout_token');
        });
    }
};
