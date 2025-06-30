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
        Schema::create('followup_sanitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('sanitation_area_uuid');
            $table->text('notes')->nullable();
            $table->text('action')->nullable();
            $table->boolean('verification')->default(0);
            $table->timestamps();

            $table->foreign('sanitation_area_uuid')->references('uuid')->on('sanitation_areas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_sanitations');
    }
};