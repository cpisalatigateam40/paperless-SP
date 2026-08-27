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
        Schema::create('report_gmp_sanitation_checks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('waktu_uuid');
            $table->foreign('waktu_uuid')->references('uuid')->on('report_gmp_waktu_pemeriksaans')->cascadeOnDelete();

            $table->uuid('section_uuid');
            $table->foreign('section_uuid')->references('uuid')->on('sections');
            $table->string('item_verifikasi');
            $table->decimal('standar_klorin', 8, 2)->nullable();
            $table->decimal('kadar_klorin', 8, 2)->nullable();
            $table->decimal('suhu', 5, 2)->nullable();
            $table->text('tindakan_koreksi')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_gmp_sanitation_checks');
    }
};
