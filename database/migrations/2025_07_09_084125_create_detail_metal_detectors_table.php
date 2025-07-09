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
        Schema::create('detail_metal_detectors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->string('hour')->nullable();
            $table->string('production_code')->nullable();
            $table->string('result_fe')->nullable();
            $table->string('result_non_fe')->nullable();
            $table->string('result_sus316')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_metal_detectors')
                ->onDelete('cascade');

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_metal_detectors');
    }
};