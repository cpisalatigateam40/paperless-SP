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
        Schema::create('master_pasteurs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->index();
            $table->uuid('product_uuid')->index();
            $table->decimal('initial_temp_min', 6, 2)->nullable();
            $table->decimal('initial_temp_max', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['area_uuid', 'product_uuid']);
        });

        Schema::create('master_pasteur_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('master_uuid')->index();
            $table->unsignedTinyInteger('step_order');   // 1-8
            $table->string('step_name');
            $table->decimal('set_time', 7, 2)->nullable();
            $table->decimal('water_temp_min', 6, 2)->nullable();
            $table->decimal('water_temp_max', 6, 2)->nullable();
            $table->decimal('pressure_min', 6, 3)->nullable();      // Mpa
            $table->decimal('pressure_max', 6, 3)->nullable();
            $table->timestamps();

            $table->unique(['master_uuid', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pasteurs');
        Schema::dropIfExists('master_pasteur_steps');
    }
};
