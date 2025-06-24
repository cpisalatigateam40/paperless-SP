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
        Schema::create('detail_sharp_tools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('sharp_tool_uuid')->nullable();
            $table->unsignedInteger('qty_start')->nullable();
            $table->unsignedInteger('qty_end')->nullable();
            $table->time('check_time_1')->nullable();
            $table->enum('condition_1', ['baik', 'rusak', 'hilang', 'tidaktersedia'])->nullable();
            $table->time('check_time_2')->nullable();
            $table->enum('condition_2', ['baik', 'rusak', 'hilang', 'tidaktersedia'])->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_sharp_tools')->onDelete('cascade');
            $table->foreign('sharp_tool_uuid')->references('uuid')->on('sharp_tools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_sharp_tools');
    }
};