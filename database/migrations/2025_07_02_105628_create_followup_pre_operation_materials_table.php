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
        Schema::create('followup_pre_operation_materials', function (Blueprint $table) {
            $table->id();
            $table->uuid('pre_operation_material_uuid');
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->timestamps();

             $table->foreign('pre_operation_material_uuid', 'fk_material_followup')
                  ->references('uuid')
                  ->on('pre_operation_materials')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_pre_operation_materials');
    }
};