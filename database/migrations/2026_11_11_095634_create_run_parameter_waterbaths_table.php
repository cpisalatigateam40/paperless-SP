<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_parameter_waterbaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('run_uuid')->index();

            // pasteurisasi | cooling | drying
            $table->string('parameter', 20);

            // --- Snapshot standar dari master (kolom kuning) ---
            // Set Temp (drying: hot zone)
            $table->decimal('set_temp_min', 8, 2)->nullable();
            $table->decimal('set_temp_max', 8, 2)->nullable();
            // Cold zone (khusus drying)
            $table->decimal('cold_zone_set_min', 8, 2)->nullable();
            $table->decimal('cold_zone_set_max', 8, 2)->nullable();
            // Set Time (menit)
            $table->decimal('set_time', 8, 2)->nullable();
            // Std CT Prod.
            $table->decimal('std_ct_min', 8, 2)->nullable();
            $table->decimal('std_ct_max', 8, 2)->nullable();

            // --- Hasil aktual (diisi QC) ---
            // Act Temp (drying: hot zone)
            $table->decimal('act_temp', 8, 2)->nullable();
            // Cold zone (khusus drying)
            $table->decimal('cold_zone_act', 8, 2)->nullable();
            // Act Time (menit)
            $table->decimal('act_time', 8, 2)->nullable();
            // Act CT Prod.
            $table->decimal('act_ct', 8, 2)->nullable();

            $table->timestamps();

            $table->unique(['run_uuid', 'parameter'], 'run_param_waterbaths_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_parameter_waterbaths');
    }
};