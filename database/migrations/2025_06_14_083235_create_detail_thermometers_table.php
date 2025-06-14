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
        Schema::create('detail_thermometers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_scale_uuid')->nullable();
            $table->uuid('thermometer_uuid')->nullable();
            $table->time('time_1')->nullable();
            $table->time('time_2')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('report_scale_uuid')->references('uuid')->on('report_scales')->onDelete('cascade');
            $table->foreign('thermometer_uuid')->references('uuid')->on('thermometers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_thermometers');
    }
};