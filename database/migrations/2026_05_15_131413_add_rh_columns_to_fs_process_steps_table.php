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
        Schema::table('fs_process_steps', function (Blueprint $table) {
            $table->double('rh_setting')->nullable()->after('room_temp_2');

            $table->double('rh_actual')->nullable()->after('rh_setting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fs_process_steps', function (Blueprint $table) {
             $table->dropColumn([
                'rh_setting',
                'rh_actual'
            ]);
        });
    }
};
