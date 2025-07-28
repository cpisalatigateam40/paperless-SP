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
        Schema::create('item_detail_prods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->uuid('formulation_uuid')->nullable();
            $table->decimal('actual_weight', 10, 3)->nullable();
            $table->string('sensory')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_process_prods')->onDelete('cascade');
            $table->foreign('formulation_uuid')->references('uuid')->on('formulations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_detail_prods');
    }
};