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
        Schema::create('detail_repair_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('equipment_uuid')->nullable();
            $table->uuid('section_uuid')->nullable();
            $table->string('repair_type')->nullable();
            $table->enum('clean_condition', ['bersih', 'kotor'])->nullable();
            $table->enum('spare_part_left', ['ada', 'tidak ada'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_repair_cleanliness')->onDelete('cascade');
            $table->foreign('equipment_uuid')->references('uuid')->on('equipments')->onDelete('cascade');
            $table->foreign('section_uuid')->references('uuid')->on('sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_repair_cleanliness');
    }
};