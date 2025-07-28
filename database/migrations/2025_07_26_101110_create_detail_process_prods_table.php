<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_process_prods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->uuid('formula_uuid')->nullable();
            $table->string('production_code')->nullable();
            $table->string('mixing_time')->nullable();
            $table->decimal('rework_kg', 10, 3)->nullable();
            $table->decimal('rework_percent', 5, 2)->nullable();
            $table->decimal('total_material', 10, 3)->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_process_prods')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
            $table->foreign('formula_uuid')->references('uuid')->on('formulas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_process_prods');
    }
};