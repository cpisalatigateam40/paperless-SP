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
        Schema::table('townsend_stuffers', function (Blueprint $table) {
            $table->decimal('avg_fla', 8, 2)->nullable()->after('avg_long');
        });

        Schema::table('hitech_stuffers', function (Blueprint $table) {
            $table->decimal('avg_fla', 8, 2)->nullable()->after('avg_long');
        });

        Schema::table('vemag_stuffers', function (Blueprint $table) {
            $table->decimal('avg_fla', 8, 2)->nullable()->after('avg_long');
        });

        Schema::table('vemag2_stuffers', function (Blueprint $table) {
            $table->decimal('avg_fla', 8, 2)->nullable()->after('avg_long');
        });

        Schema::table('handtmann_stuffers', function (Blueprint $table) {
            $table->decimal('avg_fla', 8, 2)->nullable()->after('avg_long');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('townsend_stuffers', function (Blueprint $table) {
            $table->dropColumn('avg_fla');
        });

        Schema::table('hitech_stuffers', function (Blueprint $table) {
            $table->dropColumn('avg_fla');
        });

        Schema::table('vemag_stuffers', function (Blueprint $table) {
            $table->dropColumn('avg_fla');
        });

        Schema::table('vemag2_stuffers', function (Blueprint $table) {
            $table->dropColumn('avg_fla');
        });

        Schema::table('handtmann_stuffers', function (Blueprint $table) {
            $table->dropColumn('avg_fla');
        });
    }
};
