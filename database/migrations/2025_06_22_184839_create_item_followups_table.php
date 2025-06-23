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
        Schema::create('item_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('item_process_area_cleanliness')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->text('action')->nullable();
            $table->boolean('verification')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_followups');
    }
};