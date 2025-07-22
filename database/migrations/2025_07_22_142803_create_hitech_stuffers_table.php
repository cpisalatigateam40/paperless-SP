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
        Schema::create('hitech_stuffers', function (Blueprint $table) {
            $table->id();
            $table->uuid('detail_uuid')->nullable();
            $table->integer('stuffer_speed')->nullable();
            $table->integer('trolley_total')->nullable();
            $table->float('avg_weight')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_weight_stuffers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitech_stuffers');
    }
};