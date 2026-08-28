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
        Schema::create('master_boiling_tank_standards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid');
            $table->uuid('product_uuid');

            $table->decimal('suhu_tangki_1_min', 8, 2)->nullable();
            $table->decimal('suhu_tangki_1_max', 8, 2)->nullable();

            $table->decimal('suhu_tangki_2_min', 8, 2)->nullable();
            $table->decimal('suhu_tangki_2_max', 8, 2)->nullable();

            $table->decimal('berat_mentah_min', 8, 2)->nullable();
            $table->decimal('berat_mentah_max', 8, 2)->nullable();

            $table->decimal('berat_matang_min', 8, 2)->nullable();
            $table->decimal('berat_matang_max', 8, 2)->nullable();

            $table->timestamps();

            // 1 area hanya boleh punya 1 standar per produk
            $table->unique(['area_uuid', 'product_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_boiling_tank_standards');
    }
};
