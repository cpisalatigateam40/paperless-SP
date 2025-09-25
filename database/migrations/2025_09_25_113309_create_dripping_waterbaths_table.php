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
        Schema::create('dripping_waterbaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->time('start_time_pasteur')->nullable();
            $table->time('stop_time_pasteur')->nullable();
            $table->decimal('hot_zone_temperature', 5, 2)->nullable();
            $table->decimal('cold_zone_temperature', 5, 2)->nullable();
            $table->decimal('product_temp_final', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_waterbaths')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dripping_waterbaths');
    }
};