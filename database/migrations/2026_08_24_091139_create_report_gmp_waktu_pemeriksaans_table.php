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
        Schema::create('report_gmp_waktu_pemeriksaans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('header_uuid');
            $table->foreign('header_uuid')->references('uuid')->on('report_gmp_headers')->cascadeOnDelete();

            $table->unsignedTinyInteger('waktu_ke');
            $table->time('jam_pemeriksaan')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_gmp_waktu_pemeriksaans');
    }
};
