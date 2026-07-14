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
        Schema::create('master_smoke_house_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('master_uuid');

            $table->unsignedInteger('sequence');

            $table->enum('process_name', [
                'Showering',
                'Warming',
                'Drying I',
                'Drying II',
                'Drying III',
                'Drying IV',
                'Drying V',
                'Smoking',
                'Cooking I',
                'Cooking II',
                'Evacuation',
                'Showering & Cooling Down',
            ]);

            $table->decimal('temperature_min', 5, 2)->nullable();
            $table->decimal('temperature_max', 5, 2)->nullable();

            $table->unsignedInteger('time_minutes')->nullable();

            $table->decimal('rh', 5, 2)->nullable();

            $table->decimal('core_temperature', 5, 2)->nullable();

            $table->timestamps();

            $table->foreign('master_uuid')
                ->references('uuid')
                ->on('master_smoke_houses')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_smoke_house_steps');
    }
};
