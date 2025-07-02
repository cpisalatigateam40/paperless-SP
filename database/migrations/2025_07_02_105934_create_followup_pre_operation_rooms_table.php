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
        Schema::create('followup_pre_operation_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('pre_operation_room_uuid');
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->timestamps();

            $table->foreign('pre_operation_room_uuid', 'fk_room_followup')
                  ->references('uuid')
                  ->on('pre_operation_rooms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_pre_operation_rooms');
    }
};