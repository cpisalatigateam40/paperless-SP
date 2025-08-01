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
            $table->integer('gramase')->nullable()->after('production_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_md_products', function (Blueprint $table) {
            $table->dropColumn('gramase');
        });
    }
};