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
        Schema::create('sh_thermocouple_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_detail_uuid')->nullable();
            $table->string('position_info')->nullable();
            $table->timestamps();

            $table->foreign('report_detail_uuid')->references('uuid')->on('detail_maurer_cookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sh_thermocouple_positions');
    }
};