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
        Schema::create('data_cartonings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->string('carton_code')->nullable();
            $table->integer('content_bag')->nullable();
            $table->integer('content_binded')->nullable();
            $table->float('carton_weight_standard')->nullable();
            $table->float('carton_weight_actual')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_freez_packagings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_cartonings');
    }
};