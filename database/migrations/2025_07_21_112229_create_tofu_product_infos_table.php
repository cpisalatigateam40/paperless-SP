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
        Schema::create('tofu_product_infos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->string('production_code')->nullable();
            $table->date('expired_date')->nullable();
            $table->integer('sample_amount')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_tofu_verifs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tofu_product_infos');
    }
};