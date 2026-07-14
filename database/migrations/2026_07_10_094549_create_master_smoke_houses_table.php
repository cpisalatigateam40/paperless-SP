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
        Schema::create('master_smoke_houses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('area_uuid');
            $table->uuid('product_uuid');

            $table->enum('machine_name', [
                'Fessmann',
                'Maurer',
                'Bastra',
                'Vemag',
            ]);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('area_uuid')->references('uuid')->on('areas')->cascadeOnDelete();
            $table->foreign('product_uuid')->references('uuid')->on('products')->cascadeOnDelete();

            $table->unique([
                'area_uuid',
                'product_uuid',
                'machine_name'
            ], 'master_smoke_house_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_smoke_houses');
    }
};
