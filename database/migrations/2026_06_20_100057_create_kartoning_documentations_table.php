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
        Schema::create('kartoning_documentations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->uuid('detail_uuid');
            $table->string('image');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('detail_uuid')
                ->references('uuid')
                ->on('detail_freez_packagings')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartoning_documentations');
    }
};
