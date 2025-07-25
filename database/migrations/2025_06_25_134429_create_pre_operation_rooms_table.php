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
        Schema::create('pre_operation_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('section_uuid')->nullable();
            $table->tinyInteger('condition')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_pre_operations')->onDelete('cascade');
            $table->foreign('section_uuid')->references('uuid')->on('sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_operation_rooms');
    }
};