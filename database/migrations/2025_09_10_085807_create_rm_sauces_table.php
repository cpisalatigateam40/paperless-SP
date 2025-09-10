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
        Schema::create('rm_sauces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('detail_uuid');
            $table->uuid('raw_material_uuid')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('sensory')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_sauces')->onDelete('cascade');
            $table->foreign('raw_material_uuid')->references('uuid')->on('raw_materials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_sauces');
    }
};