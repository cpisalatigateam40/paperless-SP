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
        Schema::create('loss_vacum_defects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->string('category')->nullable();
            $table->integer('pack_amount')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_prod_loss_vacums')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_vacum_defects');
    }
};