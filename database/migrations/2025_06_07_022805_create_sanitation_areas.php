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
        Schema::create('sanitation_areas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('sanitation_check_uuid')->nullable();
            $table->string('area_name')->nullable();
            $table->integer('chlorine_std')->nullable();
            $table->timestamps();

            $table->foreign('sanitation_check_uuid')->references('uuid')->on('sanitation_checks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanitation_areas');
    }
};