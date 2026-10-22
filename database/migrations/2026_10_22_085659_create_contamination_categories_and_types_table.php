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
        Schema::create('contamination_categories', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contamination_types', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('category_uuid');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_uuid')
                ->references('uuid')->on('contamination_categories')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contamination_types');
        Schema::dropIfExists('contamination_categories');
    }
};
