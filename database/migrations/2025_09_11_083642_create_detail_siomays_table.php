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
        Schema::create('detail_siomays', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('report_uuid');
            $table->time('time')->nullable();
            $table->string('process_step')->nullable();
            $table->integer('duration')->nullable();
            $table->boolean('mixing_paddle_on')->nullable();
            $table->boolean('mixing_paddle_off')->nullable();
            $table->decimal('pressure', 5, 2)->nullable();
            $table->decimal('target_temperature', 5, 2)->nullable();
            $table->decimal('actual_temperature', 5, 2)->nullable();

            $table->string('color')->nullable();
            $table->string('aroma')->nullable();
            $table->string('taste')->nullable();
            $table->string('texture')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_siomays')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_siomays');
    }
};