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
        Schema::table('process_tumblings', function (Blueprint $table) {
            $table->integer('process_duration')->nullable()->after('tumbling_process');
            $table->decimal('final_temperature', 5, 2)->nullable()->after('process_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_tumblings', function (Blueprint $table) {
            $table->dropColumn(['process_duration', 'final_temperature']);
        });
    }
};
