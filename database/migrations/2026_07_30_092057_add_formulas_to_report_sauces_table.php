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
        Schema::table('report_sauces', function (Blueprint $table) {
            $table->uuid('formula_uuid')->nullable()->after('product_uuid');
        });

        Schema::table('rm_sauces', function (Blueprint $table) {
            $table->uuid('formulation_uuid')->nullable()->after('detail_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_sauces', function (Blueprint $table) {
            $table->dropColumn('formula_uuid');
        });
        Schema::table('rm_sauces', function (Blueprint $table) {
            $table->dropColumn('formulation_uuid');
        });
    }
};
