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
        Schema::create('tofu_weight_verifs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->string('weight_category')->nullable();
            $table->integer('turus')->nullable();
            $table->integer('total')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_tofu_verifs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tofu_weight_verifs');
    }
};