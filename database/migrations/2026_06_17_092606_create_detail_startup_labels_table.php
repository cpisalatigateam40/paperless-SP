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
        Schema::create('detail_startup_labels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid'); // FK ke report_startup_labels
            $table->uuid('product_uuid')->nullable();
            $table->time('time')->nullable();
            $table->string('production_code')->nullable();
            $table->date('best_before')->nullable();
            $table->string('result')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();
 
            $table->foreign('report_uuid')
                  ->references('uuid')->on('report_startup_labels')
                  ->onDelete('cascade');
 
            $table->foreign('product_uuid')
                  ->references('uuid')->on('products')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_startup_labels');
    }
};
