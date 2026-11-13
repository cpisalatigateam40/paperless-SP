<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_step_waterbaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('run_uuid')->index();

            // pasteurisasi | cooling | drying
            $table->string('parameter', 20);

            $table->time('start_time')->nullable();
            $table->time('stop_time')->nullable();

            // Hanya pasteurisasi & cooling
            $table->decimal('initial_water_temp', 8, 2)->nullable();
            // Hanya pasteurisasi
            $table->decimal('water_temp_after_input_panel', 8, 2)->nullable();
            $table->decimal('water_temp_after_input_thermo', 8, 2)->nullable();
            $table->decimal('water_temp_final', 8, 2)->nullable();

            // Semua parameter
            $table->decimal('product_temp_final', 8, 2)->nullable();
            $table->string('remark', 255)->nullable();

            $table->timestamps();

            $table->unique(['run_uuid', 'parameter'], 'run_step_waterbaths_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_step_waterbaths');
    }
};