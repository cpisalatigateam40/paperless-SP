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
        Schema::create('followup_gmp_employee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gmp_employee_detail_id');
            $table->string('notes')->nullable();
            $table->string('action')->nullable();
            $table->boolean('verification')->default(false);
            $table->timestamps();

            $table->foreign('gmp_employee_detail_id', 'fk_followup_gmp_employee')
                ->references('id')->on('detail_gmp_employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_gmp_employee');
    }
};