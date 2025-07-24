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
        Schema::create('detail_emulsion_makings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('header_uuid')->nullable();
            $table->uuid('raw_material_uuid')->nullable();
            $table->float('weight')->nullable();
            $table->float('temperature')->nullable();
            $table->string('sensory')->nullable();
            $table->integer('aging_index')->nullable(); 
            $table->timestamps();

            $table->foreign('header_uuid')->references('uuid')->on('header_emulsion_makings')->onDelete('cascade');
            $table->foreign('raw_material_uuid')->references('uuid')->on('raw_materials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_emulsion_makings');
    }
};