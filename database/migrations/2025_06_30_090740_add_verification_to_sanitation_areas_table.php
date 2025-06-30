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
        Schema::table('sanitation_areas', function (Blueprint $table) {
             $table->boolean('verification')->nullable()->after('corrective_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanitation_areas', function (Blueprint $table) {
              $table->dropColumn('verification');
        });
    }
};