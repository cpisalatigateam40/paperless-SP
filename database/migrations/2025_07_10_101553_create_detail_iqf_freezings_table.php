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
        Schema::create('detail_iqf_freezings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->string('production_code')->nullable();
            $table->date('best_before')->nullable();
            $table->float('product_temp_before_iqf')->nullable();
            $table->time('freezing_start_time')->nullable();
            $table->integer('freezing_duration')->nullable();
            $table->string('room_temperature')->nullable();
            $table->string('suction_temperature')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_iqf_freezings')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_iqf_freezings');
    }
};