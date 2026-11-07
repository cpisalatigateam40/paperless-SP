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
        Schema::table('detail_room_cleanliness', function (Blueprint $t) {
            $t->time('verification_time')->nullable()->after('verification');
        });

        Schema::table('detail_equipment_cleanliness', function (Blueprint $t) {
            $t->time('verification_time')->nullable()->after('verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_room_cleanliness', function (Blueprint $table) {
            $table->dropColumn('verification_time');
        });

        Schema::table('detail_equipment_cleanliness', function (Blueprint $table) {
            $table->dropColumn('verification_time');
        });
    }
};
