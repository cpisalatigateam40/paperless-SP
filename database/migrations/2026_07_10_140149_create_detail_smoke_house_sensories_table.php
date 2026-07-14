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
        Schema::create('detail_smoke_house_sensories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('detail_uuid');

            $table->enum('type', [
                'main',
                'rework',
            ]);

            $table->string('appearance')->nullable();
            $table->string('color')->nullable();
            $table->string('aroma')->nullable();
            $table->string('taste')->nullable();
            $table->string('texture')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_smoke_house_sensories');
    }
};
