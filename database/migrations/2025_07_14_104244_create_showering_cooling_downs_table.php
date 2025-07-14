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
        Schema::create('showering_cooling_downs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('showering_time')->nullable();
            $table->float('room_temp_1')->nullable();
            $table->float('room_temp_2')->nullable();
            $table->float('product_temp_1')->nullable();
            $table->float('product_temp_2')->nullable();
            $table->float('time_minutes_1')->nullable();
            $table->float('time_minutes_2')->nullable();
            $table->float('product_temp_after_exit_1')->nullable();
            $table->float('product_temp_after_exit_2')->nullable();
            $table->float('product_temp_after_exit_3')->nullable();
            $table->float('avg_product_temp_after_exit')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_maurer_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showering_cooling_downs');
    }
};