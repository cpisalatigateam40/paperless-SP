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
        Schema::create('detail_baso_cookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid');

            $table->string('production_code')->nullable();
            $table->decimal('emulsion_temp', 5, 2)->nullable();
            $table->decimal('boiling_tank_temp_1', 5, 2)->nullable();
            $table->decimal('boiling_tank_temp_2', 5, 2)->nullable();

            $table->decimal('initial_weight', 5, 2)->nullable();

            $table->boolean('sensory_shape')->nullable();
            $table->boolean('sensory_taste')->nullable();
            $table->boolean('sensory_aroma')->nullable();
            $table->boolean('sensory_texture')->nullable();
            $table->boolean('sensory_color')->nullable();

            $table->decimal('final_weight', 5, 2)->nullable();

            $table->string('qc_paraf')->nullable();
            $table->string('prod_paraf')->nullable();

            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_baso_cookings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_baso_cookings');
    }
};