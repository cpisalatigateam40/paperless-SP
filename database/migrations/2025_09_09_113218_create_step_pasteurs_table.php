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
        Schema::create('step_pasteurs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid'); // FK ke detail_pasteurs
            $table->string('step_name');
            $table->integer('step_order');
            $table->enum('step_type', ['standard', 'drainage', 'finish']);
            $table->timestamps();

            $table->foreign('detail_uuid')
                  ->references('uuid')->on('detail_pasteurs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_pasteurs');
    }
};