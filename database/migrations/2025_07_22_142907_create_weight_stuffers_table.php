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
        Schema::create('weight_stuffers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stuffer_id')->constrained('detail_weight_stuffers')->onDelete('cascade')->nullable();
            $table->float('actual_weight_1')->nullable();
            $table->float('actual_weight_2')->nullable();
            $table->float('actual_weight_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_stuffers');
    }
};