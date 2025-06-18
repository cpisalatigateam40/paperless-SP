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
        Schema::create('conveyor_machines', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->time('time')->nullable();
            $table->string('machine_name')->nullable();
            $table->enum('status', ['bersih', 'kotor'])->nullable();
            $table->boolean('qc_check')->default(false)->nullable();
            $table->boolean('kr_check')->default(false)->nullable();
            $table->text('notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_conveyor_cleanliness')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conveyor_machines');
    }
};