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
        Schema::create('standard_stuffers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->integer('long_min')->nullable();
            $table->integer('long_max')->nullable();
            $table->integer('diameter')->nullable();
            $table->integer('weight_min')->nullable();
            $table->integer('weight_max')->nullable();
            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->onDelete('set null');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_stuffers');
    }
};