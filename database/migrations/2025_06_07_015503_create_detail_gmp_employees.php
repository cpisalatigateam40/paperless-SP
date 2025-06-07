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
        Schema::create('detail_gmp_employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->time('inspection_hour')->nullable();
            $table->string('section_name')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('notes')->nullable();
            $table->string('corrective_action')->nullable();
            $table->integer('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_gmp_employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_gmp_employees');
    }
};