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
        Schema::create('master_audit_packing_primer_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
 
            // food_safety | food_quality | process_compliance
            $table->string('category');
            $table->unsignedTinyInteger('item_number');
            $table->text('item_verifikasi');
            $table->boolean('is_active')->default(true);
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_audit_packing_primer_items');
    }
};
