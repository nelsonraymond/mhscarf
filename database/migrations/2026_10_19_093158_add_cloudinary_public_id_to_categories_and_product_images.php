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
    Schema::table('categories', function (Blueprint $table) {
        $table->string('cloudinary_public_id')->nullable();
    });

    Schema::table('product_images', function (Blueprint $table) {
        $table->string('cloudinary_public_id')->nullable();
    });
}

public function down(): void
{
    Schema::table('product_images', function (Blueprint $table) {
        $table->dropColumn('cloudinary_public_id');
    });

    Schema::table('categories', function (Blueprint $table) {
        $table->dropColumn('cloudinary_public_id');
    });
}
};
