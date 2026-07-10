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
        Schema::create('detail_mt_clean_photos', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('detail_uuid');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('detail_uuid')
                ->references('uuid')->on('detail_mt_cleans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_mt_clean_photos');
    }
};
