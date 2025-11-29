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
        Schema::table('detail_md_products', function (Blueprint $table) {
            $table->enum('process_type', ['Manual', 'CFS', 'Colimatic', 'Multivac'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_md_products', function (Blueprint $table) {
            $table->enum('process_type', ['Manual', 'CFS', 'Colimatic'])
                ->nullable()
                ->change();
        });
    }
};