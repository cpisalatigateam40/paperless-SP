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
        Schema::create('formulations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('raw_material_uuid')->nullable();
            $table->uuid('formula_uuid')->nullable();
            $table->string('formulation_name')->nullable();
            $table->timestamps();

            $table->foreign('formula_uuid')->references('uuid')->on('formulas')->onDelete('cascade');
            $table->foreign('raw_material_uuid')->references('uuid')->on('raw_materials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulations');
    }
};