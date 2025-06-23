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
        Schema::create('detail_foreign_objects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->time('time')->nullable();
            $table->string('production_code')->nullable();
            $table->string('contaminant_type')->nullable();
            $table->string('evidence')->nullable();
            $table->string('analysis_stage')->nullable();
            $table->string('contaminant_origin')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_foreign_objects')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_foreign_objects');
    }
};