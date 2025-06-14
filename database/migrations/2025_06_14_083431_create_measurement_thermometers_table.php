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
        Schema::create('measurement_thermometers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_thermometer_uuid')->nullable();
            $table->tinyInteger('inspection_time_index')->nullable(); // 1 atau 2
            $table->integer('standard_temperature')->nullable(); // 0 atau 100
            $table->decimal('measured_value', 6, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_thermometer_uuid')->references('uuid')->on('detail_thermometers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurement_thermometers');
    }
};