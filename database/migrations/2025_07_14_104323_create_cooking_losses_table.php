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
        Schema::create('cooking_losses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('batch_code')->nullable();
            $table->float('raw_weight')->nullable();
            $table->float('cooked_weight')->nullable();
            $table->float('loss_kg')->nullable();
            $table->float('loss_percent')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_maurer_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooking_losses');
    }
};