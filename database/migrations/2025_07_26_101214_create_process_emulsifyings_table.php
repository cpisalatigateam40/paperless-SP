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
        Schema::create('process_emulsifyings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->string('standard_mixture_temp')->nullable();
            $table->decimal('actual_mixture_temp_1', 5, 2)->nullable();
            $table->decimal('actual_mixture_temp_2', 5, 2)->nullable();
            $table->decimal('actual_mixture_temp_3', 5, 2)->nullable();
            $table->decimal('average_mixture_temp', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_process_prods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_emulsifyings');
    }
};