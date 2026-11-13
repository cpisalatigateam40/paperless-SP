<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_waterbaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->index();

            // 1 = pasteurisasi awal, 2+ = pasteurisasi ulang
            $table->unsignedTinyInteger('run_number')->default(1);

            $table->string('waterbath_number', 20)->nullable();

            // Initial Temperature: nilai aktual + snapshot range dari master
            $table->decimal('initial_temp', 8, 2)->nullable();
            $table->decimal('initial_temp_min', 8, 2)->nullable();
            $table->decimal('initial_temp_max', 8, 2)->nullable();

            // Sensori: true = Ok, false = Tidak Ok
            $table->boolean('sensor_ok')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['report_uuid', 'run_number'], 'run_waterbaths_report_run_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_waterbaths');
    }
};