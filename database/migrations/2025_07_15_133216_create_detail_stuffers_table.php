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
        Schema::create('detail_stuffers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->decimal('standard_weight', 8, 2)->nullable();
            $table->string('machine_name')->nullable();
            $table->decimal('range', 8, 2)->nullable();
            $table->decimal('avg', 8, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_stuffers')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_stuffers');
    }
};