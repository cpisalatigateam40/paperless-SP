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
        Schema::create('fragile_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->string('item_name')->nullable();
            $table->string('section_name')->nullable();
            $table->string('owner')->nullable();
            $table->unsignedInteger('quantity')->nullable();
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
        Schema::dropIfExists('fragile_items');
    }
};