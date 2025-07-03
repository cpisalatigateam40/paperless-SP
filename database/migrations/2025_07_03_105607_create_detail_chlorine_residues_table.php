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
        Schema::create('detail_chlorine_residues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->integer('day')->nullable();
            $table->decimal('result_ppm', 8, 2)->nullable();
            $table->string('remark')->nullable();
            $table->string('corrective_action')->nullable();
            $table->string('verification')->nullable();
            $table->string('verified_by')->nullable();
            $table->date('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_chlorine_residues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_chlorine_residues');
    }
};