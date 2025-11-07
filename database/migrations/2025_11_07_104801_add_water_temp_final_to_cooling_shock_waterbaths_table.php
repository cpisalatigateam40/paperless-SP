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
        Schema::table('cooling_shock_waterbaths', function (Blueprint $table) {
            $table->decimal('water_temp_final', 8, 2)->nullable()->after('water_temp_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cooling_shock_waterbaths', function (Blueprint $table) {
            $table->dropColumn('water_temp_final');
        });
    }
};