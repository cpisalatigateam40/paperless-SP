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
        Schema::create('followup_detail_equipment_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('detail_equipment_uuid');
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->timestamps();

            $table->foreign('detail_equipment_uuid', 'fk_equipment_detail')
                ->references('uuid')
                ->on('detail_equipment_cleanliness')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_detail_equipment_cleanliness');
    }
};