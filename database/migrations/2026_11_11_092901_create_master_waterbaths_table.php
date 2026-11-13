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
        Schema::create('master_waterbaths', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('area_uuid')->index();
            $table->uuid('product_uuid')->index();

            // Initial Temperature (range). Kalau bukan range, isi min saja.
            $table->decimal('initial_temp_min', 8, 2)->nullable();
            $table->decimal('initial_temp_max', 8, 2)->nullable();

            $table->timestamps();

            $table->unique(['area_uuid', 'product_uuid'], 'master_waterbaths_area_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_waterbaths');
    }
};
