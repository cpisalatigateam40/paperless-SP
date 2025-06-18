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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->string('product_name')->nullable();
            $table->string('brand')->nullable();
            $table->float('nett_weight')->nullable();
            $table->integer('shelf_life')->nullable();
            $table->timestamps();

            $table->foreign('area_uuid')
                ->references('uuid')
                ->on('areas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};