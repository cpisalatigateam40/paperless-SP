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
        Schema::table('master_smoke_house_steps', function (Blueprint $table) {
            $table->integer('time_minutes_max')->nullable()->after('time_minutes');
            $table->decimal('core_temperature_max', 8, 2)->nullable()->after('core_temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_smoke_house_steps', function (Blueprint $table) {
            $table->dropColumn(['time_minutes_max', 'core_temperature_max']);
        });
    }
};
