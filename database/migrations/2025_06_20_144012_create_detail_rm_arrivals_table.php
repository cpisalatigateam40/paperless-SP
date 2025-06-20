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
        Schema::create('detail_rm_arrivals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('raw_material_uuid')->nullable();
            $table->time('time')->nullable();
            $table->string('production_code')->nullable();
            $table->float('temperature')->nullable();
            $table->string('packaging_condition')->nullable();
            $table->string('sensorial_condition')->nullable();
            $table->text('problem')->nullable()->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_rm_arrivals')->onDelete('cascade');
            $table->foreign('raw_material_uuid')->references('uuid')->on('raw_materials')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rm_arrivals');
    }
};
