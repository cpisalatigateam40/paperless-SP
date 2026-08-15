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
        Schema::create('report_alat_verifications', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('area_uuid');
            $table->date('date');
            $table->string('shift');
            $table->string('created_by');
            $table->string('known_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_alat_verifications');
    }
};
