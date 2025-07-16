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
        Schema::create('fs_process_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('step_name')->nullable();
            $table->string('time_minutes_1')->nullable();
            $table->string('time_minutes_2')->nullable();
            $table->string('room_temp_1')->nullable();
            $table->string('room_temp_2')->nullable();
            $table->string('air_circulation_1')->nullable();
            $table->string('air_circulation_2')->nullable();
            $table->string('product_temp_1')->nullable();
            $table->string('product_temp_2')->nullable();
            $table->string('actual_product_temp')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_fessman_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fs_process_steps');
    }
};