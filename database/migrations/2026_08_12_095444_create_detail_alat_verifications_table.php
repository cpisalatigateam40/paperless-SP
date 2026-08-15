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
        Schema::create('detail_alat_verifications', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('report_alat_verification_uuid');

            // Jenis & kode alat (polymorphic: 'scale' | 'thermometer' | ... nanti bisa ditambah)
            $table->string('alat_type');
            $table->uuid('alat_uuid');

            // Titik ukur yang diverifikasi (kg untuk timbangan, °C untuk thermometer, dst)
            $table->string('titik_ukur');

            // Hasil pembacaan alat pada titik ukur tsb
            $table->decimal('nilai_baca', 10, 2);

            // Jam pemeriksaan tunggal
            $table->time('check_time')->nullable();

            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_alat_verification_uuid')
                ->references('uuid')->on('report_alat_verifications')
                ->cascadeOnDelete();

            $table->index(['alat_type', 'alat_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_alat_verifications');
    }
};
