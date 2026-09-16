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
        Schema::table('detail_sauces', function (Blueprint $table) {
            $table->decimal('brix', 5, 2)->nullable()->after('actual_temperature');
            $table->decimal('salinity', 5, 2)->nullable()->after('brix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_sauces', function (Blueprint $table) {
            $table->dropColumn(['brix', 'salinity']);
        });
    }
};
