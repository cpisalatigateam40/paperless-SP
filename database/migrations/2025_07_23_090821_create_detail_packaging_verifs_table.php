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
        Schema::create('detail_packaging_verifs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('product_uuid')->nullable();
            $table->time('time')->nullable();
            $table->string('production_code')->nullable();
            $table->date('expired_date')->nullable();
            $table->string('qc_verif')->nullable();
            $table->string('kr_verif')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_packaging_verifs')->onDelete('cascade');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_packaging_verifs');
    }
};