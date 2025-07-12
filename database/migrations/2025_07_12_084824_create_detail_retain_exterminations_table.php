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
        Schema::create('detail_retain_exterminations', function (Blueprint $table) {
            $table->id();
             $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->string('retain_name')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('retain_condition')->nullable();
            $table->string('shape')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('quantity_kg', 8, 2)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('report_uuid');
            $table->foreign('report_uuid')
                  ->references('uuid')
                  ->on('report_retain_exterminations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retain_exterminations');
    }
};