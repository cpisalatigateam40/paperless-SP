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
        Schema::create('detail_smoke_house_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('detail_uuid');

            $table->unsignedInteger('sequence');

            $table->string('process_name');

            // Snapshot Setting dari Master
            $table->string('setting_temp')->nullable();
            $table->string('setting_time')->nullable();
            $table->decimal('setting_rh', 5, 2)->nullable();
            $table->string('setting_ct')->nullable();

            // Actual
            $table->decimal('actual_temp', 5, 2)->nullable();
            $table->unsignedInteger('actual_time')->nullable();
            $table->decimal('actual_rh', 5, 2)->nullable();
            $table->string('actual_ct')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_smoke_house_steps');
    }
};
