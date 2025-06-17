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
        Schema::create('detail_equipment_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_re_uuid')->nullable();
            $table->uuid('equipment_uuid')->nullable();
            $table->uuid('equipment_part_uuid')->nullable();
            $table->string('condition')->nullable(); // Bersih / Kotor
            $table->text('notes')->nullable(); // Keterangan jika kotor
            $table->text('corrective_action')->nullable();
            $table->text('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_re_uuid')->references('uuid')->on('report_re_cleanliness')->onDelete('cascade');
            $table->foreign('equipment_uuid')->references('uuid')->on('equipments')->onDelete('restrict');
            $table->foreign('equipment_part_uuid')->references('uuid')->on('equipment_parts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_equipment_cleanliness');
    }
};