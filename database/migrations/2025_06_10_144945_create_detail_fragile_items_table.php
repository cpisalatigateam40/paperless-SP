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
        Schema::create('detail_fragile_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_fragile_item_uuid')->nullable();
            $table->uuid('fragile_item_uuid')->nullable();
            $table->integer('actual_quantity')->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_fragile_item_uuid')
                ->references('uuid')
                ->on('report_fragile_items')
                ->onDelete('cascade');

            $table->foreign('fragile_item_uuid')
                ->references('uuid')
                ->on('fragile_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_fragile_items');
    }
};