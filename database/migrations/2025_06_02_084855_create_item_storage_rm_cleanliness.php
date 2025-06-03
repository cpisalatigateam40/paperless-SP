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
        Schema::create('item_storage_rm_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('detail_uuid')->nullable();
            $table->string('item')->nullable();
            $table->string('condition')->nullable();
            $table->string('notes')->nullable();
            $table->string('corrective_action')->nullable();
            $table->integer('verification')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_storage_rm_cleanliness')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_storage_rm_cleanliness');
    }
};