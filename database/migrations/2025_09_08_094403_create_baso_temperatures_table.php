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
        Schema::create('baso_temperatures', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid');

            $table->enum('time_type', ['awal', 'akhir'])->nullable();
            $table->time('time_recorded')->nullable();

            $table->decimal('baso_temp_1', 5, 2)->nullable();
            $table->decimal('baso_temp_2', 5, 2)->nullable();
            $table->decimal('baso_temp_3', 5, 2)->nullable();
            $table->decimal('baso_temp_4', 5, 2)->nullable();
            $table->decimal('baso_temp_5', 5, 2)->nullable();
            $table->decimal('avg_baso_temp', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')
                ->references('uuid')
                ->on('detail_baso_cookings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baso_temperatures');
    }
};