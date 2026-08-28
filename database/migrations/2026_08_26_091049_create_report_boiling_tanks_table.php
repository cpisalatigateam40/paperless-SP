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
        Schema::create('report_boiling_tanks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();

            $table->date('date');
            $table->string('shift');
            $table->uuid('product_uuid')->nullable();
            $table->string('product_code')->nullable(); // ex: "QF27801AA0 - QF27807AA0"
            $table->decimal('gramasi', 8, 2)->nullable();

            $table->string('line_boiling_tank')->nullable(); // ex: "Line 1"
            $table->time('waktu_proses_start')->nullable();
            $table->time('waktu_proses_end')->nullable();

            $table->string('status')->default('draft'); // draft | selesai
            $table->text('link_kurva')->nullable(); // Catatan & Dokumentasi

            $table->string('created_by')->nullable(); // Diperiksa oleh - QC Inspector

            $table->string('known_by')->nullable(); // Diketahui oleh - Foreman/SPV Produksi
            $table->timestamp('known_at')->nullable();

            $table->string('approved_by')->nullable(); // Disetujui oleh - Supervisor QC
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_boiling_tanks');
    }
};
