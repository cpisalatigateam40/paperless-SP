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
        Schema::create('standard_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('step_uuid'); // FK ke step_pasteurs
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('water_temp', 8, 2)->nullable();
            $table->decimal('pressure', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('step_uuid')
                  ->references('uuid')->on('step_pasteurs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_steps');
    }
};