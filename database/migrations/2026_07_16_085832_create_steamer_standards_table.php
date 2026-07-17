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
        Schema::create('steamer_standards', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('area_uuid');
            $table->uuid('product_uuid');
            $table->decimal('room_temp_min', 5, 2)->nullable();
            $table->decimal('room_temp_max', 5, 2)->nullable();
            $table->unsignedInteger('setup_time_min')->nullable(); // menit
            $table->unsignedInteger('setup_time_max')->nullable(); // menit
            $table->decimal('core_temp_min', 5, 2)->nullable();
            $table->decimal('core_temp_max', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->cascadeOnDelete();
            $table->foreign('product_uuid')->references('uuid')->on('products')->cascadeOnDelete();

            $table->unique(['area_uuid', 'product_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steamer_standards');
    }
};
