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
        Schema::create('checklist_packaging_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->string('in_cutting_manual_1')->nullable();
            $table->string('in_cutting_manual_2')->nullable();
            $table->string('in_cutting_manual_3')->nullable();
            $table->string('in_cutting_manual_4')->nullable();
            $table->string('in_cutting_manual_5')->nullable();
            $table->string('in_cutting_machine_1')->nullable();
            $table->string('in_cutting_machine_2')->nullable();
            $table->string('in_cutting_machine_3')->nullable();
            $table->string('in_cutting_machine_4')->nullable();
            $table->string('in_cutting_machine_5')->nullable();
            $table->string('packaging_thermoformer_1')->nullable();
            $table->string('packaging_thermoformer_2')->nullable();
            $table->string('packaging_thermoformer_3')->nullable();
            $table->string('packaging_thermoformer_4')->nullable();
            $table->string('packaging_thermoformer_5')->nullable();
            $table->string('packaging_manual_1')->nullable();
            $table->string('packaging_manual_2')->nullable();
            $table->string('packaging_manual_3')->nullable();
            $table->string('packaging_manual_4')->nullable();
            $table->string('packaging_manual_5')->nullable();
            $table->string('sealing_condition_1')->nullable();
            $table->string('sealing_condition_2')->nullable();
            $table->string('sealing_condition_3')->nullable();
            $table->string('sealing_condition_4')->nullable();
            $table->string('sealing_condition_5')->nullable();
            $table->string('sealing_vacuum_1')->nullable();
            $table->string('sealing_vacuum_2')->nullable();
            $table->string('sealing_vacuum_3')->nullable();
            $table->string('sealing_vacuum_4')->nullable();
            $table->string('sealing_vacuum_5')->nullable();
            $table->integer('content_per_pack_1')->nullable();
            $table->integer('content_per_pack_2')->nullable();
            $table->integer('content_per_pack_3')->nullable();
            $table->integer('content_per_pack_4')->nullable();
            $table->integer('content_per_pack_5')->nullable();
            $table->decimal('standard_weight', 8, 2)->nullable();
            $table->decimal('actual_weight_1', 8, 2)->nullable();
            $table->decimal('actual_weight_2', 8, 2)->nullable();
            $table->decimal('actual_weight_3', 8, 2)->nullable();
            $table->decimal('actual_weight_4', 8, 2)->nullable();
            $table->decimal('actual_weight_5', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_packaging_verifs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_packaging_details');
    }
};