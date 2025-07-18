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
        Schema::create('detail_retain_samples', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->string('production_code')->nullable();
            $table->decimal('room_temp', 5, 2)->nullable();
            $table->decimal('suction_temp', 5, 2)->nullable();
            $table->decimal('display_speed', 5, 2)->nullable();
            $table->decimal('actual_speed', 5, 2)->nullable();
            $table->time('time_in')->nullable();
            $table->enum('line_type', ['ABF', 'IQF'])->nullable();
            $table->string('signature_in')->nullable();
            $table->string('signature_out')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_retain_samples')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retain_samples');
    }
};