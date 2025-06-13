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
        Schema::create('measurement_scales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_scale_uuid')->nullable();
            $table->tinyInteger('inspection_time_index'); // 1 atau 2
            $table->integer('standard_weight'); // 1000, 5000, 10000
            $table->decimal('measured_value', 8, 2);
            $table->timestamps();

            $table->foreign('detail_scale_uuid')->references('uuid')->on('detail_scales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurement_scales');
    }
};