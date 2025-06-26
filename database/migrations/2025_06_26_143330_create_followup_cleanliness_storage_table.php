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
        Schema::create('followup_cleanliness_storage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_storage_rm_cleanliness_id');
            $table->string('notes')->nullable();
            $table->string('corrective_action')->nullable();
            $table->boolean('verification')->default(false);
            $table->timestamps();

            // Ganti dengan nama constraint yang lebih pendek
            $table->foreign('item_storage_rm_cleanliness_id', 'fk_followup_item')
                ->references('id')->on('item_storage_rm_cleanliness')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_cleanliness_storage');
    }
};