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
        Schema::create('form_numbers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('area_uuid');
            $table->string('report_type');
            $table->string('form_number');
            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->cascadeOnDelete();
            $table->unique(['area_uuid', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_numbers');
    }
};
