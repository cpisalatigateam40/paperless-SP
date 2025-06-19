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
        Schema::create('detail_solvents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('report_uuid')->nullable();
            $table->uuid('solvent_uuid')->nullable();
            $table->boolean('verification_result')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('reverification_action')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_solvents')->onDelete('cascade');
            $table->foreign('solvent_uuid')->references('uuid')->on('solvent_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_solvents');
    }
};