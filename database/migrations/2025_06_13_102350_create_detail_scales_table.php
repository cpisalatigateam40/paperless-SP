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
        Schema::create('detail_scales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_scale_uuid')->nullable();
            $table->uuid('scale_uuid')->nullable();
            $table->time('time_1')->nullable();
            $table->time('time_2')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_scale_uuid')->references('uuid')->on('report_scales')->onDelete('cascade');

            $table->foreign('scale_uuid')->references('uuid')->on('scales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_scales');
    }
};