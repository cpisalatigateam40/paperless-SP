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
        Schema::table('master_boiling_tank_standards', function (Blueprint $table) {
            $table->decimal('actual_core_temp_min', 8, 2)->nullable()->after('suhu_tangki_2_max');
            $table->decimal('actual_core_temp_max', 8, 2)->nullable()->after('actual_core_temp_min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_boiling_tank_standards', function (Blueprint $table) {
            $table->dropColumn(['actual_core_temp_min', 'actual_core_temp_max']);
        });
    }
};
