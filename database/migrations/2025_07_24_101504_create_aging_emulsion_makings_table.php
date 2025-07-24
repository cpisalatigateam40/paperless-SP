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
        Schema::create('aging_emulsion_makings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('header_uuid')->nullable();
            $table->string('start_aging')->nullable();
            $table->string('finish_aging')->nullable();
            $table->string('emulsion_result')->nullable();
            $table->timestamps();

            $table->foreign('header_uuid')->references('uuid')->on('header_emulsion_makings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aging_emulsion_makings');
    }
};