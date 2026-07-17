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
        Schema::create('steamer_cooking_details', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('batch_uuid');
            $table->string('production_code')->nullable();
            $table->time('start_process')->nullable();
            $table->time('end_process')->nullable();
            $table->unsignedInteger('setup_time')->nullable(); // menit
            $table->decimal('room_temp', 5, 2)->nullable(); // suhu ruang aktual, std 90 ± 2°C

            // Sensori
            $table->string('sensory_bentuk')->nullable();
            $table->string('sensory_warna')->nullable();
            $table->string('sensory_aroma')->nullable();
            $table->string('sensory_rasa')->nullable();
            $table->string('sensory_tekstur')->nullable();

            $table->timestamps();

            $table->foreign('batch_uuid')->references('uuid')->on('steamer_cooking_batches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steamer_cooking_details');
    }
};