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
        Schema::create('verification_equipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('equipment_uuid')->nullable();
            $table->tinyInteger('condition')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')->on('report_product_changes')
                ->onDelete('cascade');

            $table->foreign('equipment_uuid')
                ->references('uuid')->on('equipments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_equipments');
    }
};