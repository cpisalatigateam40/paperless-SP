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
        Schema::create('steamer_cooking_core_temps', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('detail_uuid');
            $table->unsignedInteger('sequence')->default(1); // 1, 2, 3, dst
            $table->decimal('temp_value', 5, 2); // std 80 - 85°C
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('steamer_cooking_details')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steamer_cooking_core_temps');
    }
};