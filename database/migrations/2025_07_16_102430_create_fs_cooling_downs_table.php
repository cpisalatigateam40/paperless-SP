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
        Schema::create('fs_cooling_downs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('step_name')->nullable();
            $table->string('time_minutes_1')->nullable();
            $table->string('time_minutes_2')->nullable();
            $table->string('rh_1')->nullable();
            $table->string('rh_2')->nullable();
            $table->string('product_temp_after_exit_1')->nullable();
            $table->string('product_temp_after_exit_2')->nullable();
            $table->string('product_temp_after_exit_3')->nullable();
            $table->string('avg_product_temp_after_exit')->nullable();
            $table->string('raw_weight')->nullable();
            $table->string('cooked_weight')->nullable();
            $table->string('loss_kg')->nullable();
            $table->string('loss_percent')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_fessman_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fs_cooling_downs');
    }
};