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
        Schema::create('master_waterbath_parameters', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('master_waterbath_uuid')
                ->constrained('master_waterbaths', 'uuid')
                ->cascadeOnDelete();

            // pasteurisasi | cooling | drying
            $table->string('parameter', 20);

            // Set Temp (°C) range.
            // Untuk drying: ini = HOT ZONE
            $table->decimal('set_temp_min', 8, 2)->nullable();
            $table->decimal('set_temp_max', 8, 2)->nullable();

            // Khusus drying: COLD ZONE (pasteurisasi & cooling dibiarkan null)
            $table->decimal('cold_zone_temp_min', 8, 2)->nullable();
            $table->decimal('cold_zone_temp_max', 8, 2)->nullable();

            // Set Time (menit)
            $table->decimal('set_time', 8, 2)->nullable();

            // Std CT Prod. (°C) range
            $table->decimal('std_ct_min', 8, 2)->nullable();
            $table->decimal('std_ct_max', 8, 2)->nullable();

            $table->timestamps();

            $table->unique(['master_waterbath_uuid', 'parameter'], 'master_wb_param_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_waterbath_parameters');
    }
};
