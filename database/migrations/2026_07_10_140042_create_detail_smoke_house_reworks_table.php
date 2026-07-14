<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_smoke_house_reworks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('detail_uuid');

            $table->unsignedInteger('smoke_house_no');

            $table->unsignedInteger('trolley_count');

            $table->unsignedInteger('stick_count');

            $table->dateTime('start_process')->nullable();
            $table->dateTime('end_process')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_smoke_house_reworks');
    }
};
