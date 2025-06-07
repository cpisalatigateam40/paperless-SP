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
        Schema::create('sanitation_results', function (Blueprint $table) {
            $table->id();
            $table->uuid('sanitation_area_uuid')->nullable();
            $table->enum('hour_to', ['1', '2'])->nullable();
            $table->decimal('chlorine_level', 5, 2)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('notes')->nullable();
            $table->string('corrective_action')->nullable();
            $table->timestamps();

            $table->foreign('sanitation_area_uuid')
                ->references('uuid')
                ->on('sanitation_areas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanitation_results');
    }
};