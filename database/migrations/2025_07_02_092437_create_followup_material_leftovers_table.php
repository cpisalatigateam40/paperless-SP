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
        Schema::create('followup_material_leftovers', function (Blueprint $table) {
            $table->id();
            $table->uuid('material_leftover_uuid');
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->timestamps();

            $table->foreign('material_leftover_uuid')
                ->references('uuid')
                ->on('verification_material_leftovers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_material_leftovers');
    }
};