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
        Schema::create('report_audit_packing_primers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
 
            $table->uuid('area_uuid');
            $table->uuid('section_uuid'); // "Area" di form (mis. Packing) -> Section
            $table->uuid('product_uuid')->nullable();
 
            $table->date('date');
            $table->string('shift')->nullable();
            $table->string('line')->nullable();
            $table->string('production_code')->nullable();
            $table->string('karyawan')->nullable();
 
            // Bagian C: Hasil Audit & Kriteria Penilaian
            $table->string('audit_score')->nullable();  // contoh: "10/10", "9/10", "<=8/10"
            $table->text('tindakan')->nullable();        // tindakan sesuai kriteria yg dipilih
 
            $table->uuid('created_by')->nullable();
            $table->uuid('known_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
 
            $table->timestamps();
 
            $table->foreign('area_uuid')->references('uuid')->on('areas')->cascadeOnDelete();
            $table->foreign('section_uuid')->references('uuid')->on('sections')->cascadeOnDelete();
            $table->foreign('product_uuid')->references('uuid')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_audit_packing_primers');
    }
};
