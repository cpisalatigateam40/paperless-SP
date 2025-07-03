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
        Schema::create('followup_chlorine_residues', function (Blueprint $table) {
            $table->id();
            $table->uuid('detail_chlorine_residue_uuid');
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->timestamps();

            $table->foreign('detail_chlorine_residue_uuid', 'fk_detail_cr_followup')
                ->references('uuid')
                ->on('detail_chlorine_residues')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_chlorine_residues');
    }
};