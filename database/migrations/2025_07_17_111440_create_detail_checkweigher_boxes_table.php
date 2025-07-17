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
        Schema::create('detail_checkweigher_boxes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->time('time_inspection')->nullable();
            $table->string('production_code')->nullable();
            $table->date('expired_date')->nullable();
            $table->string('program_number')->nullable();
            $table->float('checkweigher_weight_gr')->nullable();
            $table->float('manual_weight_gr')->nullable();
            $table->boolean('double_item')->default(false)->nullable();
            $table->boolean('weight_under')->default(false)->nullable();
            $table->boolean('weight_over')->default(false)->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('verification')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_checkweigher_boxes')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_checkweigher_boxes');
    }
};