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
        Schema::create('case_stuffers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stuffer_id')->constrained('detail_weight_stuffers')->onDelete('cascade')->nullable();
            $table->integer('actual_case_1')->nullable();
            $table->integer('actual_case_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_stuffers');
    }
};