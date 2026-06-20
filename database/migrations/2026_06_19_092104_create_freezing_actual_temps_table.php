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
        Schema::create('freezing_actual_temps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('freezing_uuid');
            $table->decimal('actual_temp', 10, 7)->nullable();

            $table->timestamps();

            $table->foreign('freezing_uuid')
                ->references('uuid')
                ->on('data_freezings')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freezing_actual_temps');
    }
};
