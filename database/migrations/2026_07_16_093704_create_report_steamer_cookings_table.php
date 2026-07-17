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
        Schema::create('report_steamer_cookings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('area_uuid');
            $table->date('date');
            $table->string('shift');
            $table->uuid('product_uuid');
            $table->string('product_code_range')->nullable(); // ex: QF27801AA0 - QF27807AA0
            $table->decimal('gramase', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('curve_url')->nullable(); // link kurva pemasakan

            // Signature
            $table->string('created_by')->nullable();  // Diperiksa oleh - QC Inspector
            $table->string('known_by')->nullable();     // Diketahui oleh - Foreman/SPV Produksi
            $table->string('approved_by')->nullable();  // Disetujui oleh - Supervisor QC
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->cascadeOnDelete();
            $table->foreign('product_uuid')->references('uuid')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_steamer_cookings');
    }
};