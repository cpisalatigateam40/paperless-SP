<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_thawings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('report_uuid');
            $table->uuid('raw_material_uuid')->nullable();

            $table->time('start_thawing_time')->nullable();
            $table->time('end_thawing_time')->nullable();

            $table->string('package_condition')->nullable();
            $table->string('production_code')->nullable();
            $table->string('qty')->nullable();

            $table->string('room_condition')->nullable();

            $table->time('inspection_time')->nullable();

            $table->decimal('room_temp', 5, 2)->nullable();
            $table->decimal('water_temp', 5, 2)->nullable();
            $table->decimal('product_temp', 5, 2)->nullable();

            $table->string('product_condition')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_thawings')
                ->onDelete('cascade');

            $table->foreign('raw_material_uuid')
                ->references('uuid')
                ->on('raw_materials')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_thawings');
    }
};
