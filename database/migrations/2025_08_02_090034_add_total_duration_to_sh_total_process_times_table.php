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
        Schema::table('sh_total_process_times', function (Blueprint $table) {
            $table->integer('total_duration')->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sh_total_process_times', function (Blueprint $table) {
            $table->dropColumn('total_duration');
        });
    }
};