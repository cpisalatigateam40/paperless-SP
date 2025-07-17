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
        Schema::create('data_freezings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->float('start_product_temp')->nullable();
            $table->float('end_product_temp')->nullable();
            $table->float('iqf_room_temp')->nullable();
            $table->float('iqf_suction_temp')->nullable();
            $table->unsignedInteger('freezing_time_display')->nullable();
            $table->unsignedInteger('freezing_time_actual')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_freez_packagings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_freezings');
    }
};