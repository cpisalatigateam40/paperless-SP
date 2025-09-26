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
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->uuid('rework_product_uuid')->nullable()->after('product_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->dropColumn('rework_product_uuid');
        });
    }
};