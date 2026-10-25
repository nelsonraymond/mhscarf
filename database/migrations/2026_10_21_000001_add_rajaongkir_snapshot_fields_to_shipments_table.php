<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_id')->nullable()->after('order_id');
            $table->string('courier_code', 24)->nullable()->after('courier');
            $table->unsignedInteger('weight_gram')->nullable()->after('shipping_cost');
            $table->string('estimated_delivery', 80)->nullable()->after('weight_gram');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'destination_id',
                'courier_code',
                'weight_gram',
                'estimated_delivery',
            ]);
        });
    }
};
