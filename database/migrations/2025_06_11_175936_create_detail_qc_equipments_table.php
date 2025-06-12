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
        Schema::create('detail_qc_equipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_qc_equipment_uuid')->nullable();
            $table->uuid('qc_equipment_uuid')->nullable();
            $table->integer('actual_quantity')->nullable();
            $table->string('time_start')->nullable();
            $table->string('time_end')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_qc_equipment_uuid')
                ->references('uuid')
                ->on('report_qc_equipments')
                ->onDelete('cascade');

            $table->foreign('qc_equipment_uuid')
                ->references('uuid')
                ->on('qc_equipments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_qc_equipments');
    }
};