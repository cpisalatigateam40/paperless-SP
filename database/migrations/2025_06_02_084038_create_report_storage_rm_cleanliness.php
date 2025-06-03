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
        Schema::create('report_storage_rm_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('date')->nullable();
            $table->string('shift')->nullable();
            $table->string('room_name')->nullable();
            $table->string('created_by')->nullable();
            $table->string('known_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_storage_rm_cleanliness');
    }
};