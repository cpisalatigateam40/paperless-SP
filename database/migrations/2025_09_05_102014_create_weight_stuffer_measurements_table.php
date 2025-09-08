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
        Schema::create('weight_stuffer_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stuffer_id')->constrained('detail_weight_stuffers')->cascadeOnDelete();
            $table->float('actual_weight')->nullable();
            $table->float('actual_long')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_stuffer_measurements');
    }
};