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
        Schema::create('metal_detectors', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('area_uuid');
            $table->string('merk');
            $table->string('type_model');
            $table->string('no_series');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metal_detectors');
    }
};
