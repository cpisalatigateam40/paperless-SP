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
        Schema::create('detail_audit_packing_primers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('report_uuid');
            $table->uuid('item_uuid');
 
            $table->enum('verifikasi', ['yes', 'no'])->nullable();
            $table->text('keterangan')->nullable();
 
            $table->timestamps();
 
            $table->foreign('report_uuid')
                ->references('uuid')->on('report_audit_packing_primers')
                ->cascadeOnDelete();
 
            $table->foreign('item_uuid')
                ->references('uuid')->on('master_audit_packing_primer_items')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_audit_packing_primers');
    }
};
