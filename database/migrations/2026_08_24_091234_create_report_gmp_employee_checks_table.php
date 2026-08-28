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
        Schema::create('report_gmp_employee_checks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('waktu_uuid');
            $table->foreign('waktu_uuid')->references('uuid')->on('report_gmp_waktu_pemeriksaans')->cascadeOnDelete();

            $table->uuid('section_uuid');
            $table->foreign('section_uuid')->references('uuid')->on('sections');
            $table->string('employee_name');
            $table->boolean('seragam_apd_lengkap')->nullable();
            $table->boolean('sarung_tangan_utuh')->nullable();
            $table->boolean('sepatu_boots_bersih')->nullable();
            $table->boolean('tidak_pakai_perhiasan')->nullable();
            $table->boolean('kuku_tangan_bersih')->nullable();
            $table->boolean('kuku_tidak_panjang')->nullable();
            $table->boolean('perilaku_kerja')->nullable();
            $table->boolean('potensi_cross_contamination')->nullable();
            $table->text('tindakan_koreksi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_gmp_employee_checks');
    }
};
