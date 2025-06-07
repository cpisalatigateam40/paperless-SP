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
        Schema::create('sanitation_checks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->time('hour_1')->nullable();
            $table->time('hour_2')->nullable();
            $table->integer('verification')->nullable();
            $table->unsignedBigInteger('report_gmp_employee_id')->nullable();
            $table->timestamps();

            $table->foreign('area_uuid')
                ->references('uuid')
                ->on('areas')
                ->onDelete('set null');

            $table->foreign('report_gmp_employee_id')
                ->references('id')
                ->on('report_gmp_employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanitation_checks');
    }
};