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
        Schema::create('maurer_standards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('product_uuid')->nullable();
            $table->uuid('process_step_uuid')->nullable();
            $table->decimal('st_min', 5, 2)->nullable();
            $table->decimal('st_max', 5, 2)->nullable();
            $table->integer('time_minute')->nullable();
            $table->decimal('rh_min', 5, 2)->nullable();
            $table->decimal('rh_max', 5, 2)->nullable();
            $table->decimal('ct_min', 5, 2)->nullable();
            $table->decimal('ct_max', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('products')
                ->onDelete('set null');

            $table->foreign('process_step_uuid')
                ->references('uuid')
                ->on('maurer_processing_steps')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maurer_standards');
    }
};