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
        Schema::create('sh_process_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('step_name')->nullable();
            $table->float('room_temperature_1')->nullable();
            $table->float('room_temperature_2')->nullable();
            $table->float('rh_1')->nullable();
            $table->float('rh_2')->nullable();
            $table->float('time_minutes_1')->nullable();
            $table->float('time_minutes_2')->nullable();
            $table->float('product_temperature_1')->nullable();
            $table->float('product_temperature_2')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_maurer_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sh_process_steps');
    }
};