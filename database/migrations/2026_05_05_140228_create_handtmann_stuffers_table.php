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
        Schema::create('handtmann_stuffers', function (Blueprint $table) {
            $table->id();
            $table->uuid('detail_uuid')->nullable()->index();
            $table->integer('stuffer_speed')->nullable();
            $table->integer('trolley_total')->nullable();
            $table->double('avg_weight')->nullable();
            $table->double('avg_long')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handtmann_stuffers');
    }
};
