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
        Schema::create('steamer_cooking_batches', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('report_uuid');
            $table->string('steamer_number');
            $table->unsignedInteger('trolley_count')->nullable();
            $table->unsignedInteger('tray_per_trolley')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_steamer_cookings')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steamer_cooking_batches');
    }
};