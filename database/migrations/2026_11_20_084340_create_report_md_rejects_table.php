<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_md_rejects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->index();
            $table->date('date');
            $table->string('shift', 50);
            $table->string('line', 100);

            $table->string('created_by');
            $table->string('known_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->boolean('is_audit')->default(false);
            $table->uuid('source_uuid')->nullable()->index();

            $table->timestamps();
        });

        // Section A: verifikasi alat (test piece)
        Schema::create('report_md_reject_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->index();
            $table->string('test_piece', 50);           // Fe 1.5 mm | Non-Fe 2 mm | SUS 2.5 mm
            $table->string('result', 20)->nullable();   // detected | not_detected
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_md_rejects')->cascadeOnDelete();
        });

        // Section B: data produk (dinamis)
        Schema::create('report_md_reject_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->index();
            $table->uuid('product_uuid')->nullable()->index();

            $table->time('time');                                   // Jam
            $table->string('production_code', 100);
            $table->unsignedInteger('reject_qty')->default(0);      // pcs/pack

            $table->string('recheck_result', 20)->nullable();       // detected | not_detected
            $table->boolean('contamination_found')->nullable();     // Ada / Tidak

            $table->json('suspected_causes')->nullable();           // array key penyebab
            $table->string('suspected_cause_other')->nullable();    // isian "Lainnya"

            $table->text('corrective_action')->nullable();
            $table->string('disposition', 20)->nullable();          // release | hold | reject
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_md_rejects')->cascadeOnDelete();
            $table->foreign('product_uuid')->references('uuid')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_md_reject_details');
        Schema::dropIfExists('report_md_reject_verifications');
        Schema::dropIfExists('report_md_rejects');
    }
};