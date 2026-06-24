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
        Schema::table('weight_stuffer_measurements', function (Blueprint $table) {
            $table->decimal('actual_fla',8,2)->nullable()->after('actual_long');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weight_stuffer_measurements', function (Blueprint $table) {
            $table->dropColumn('actual_fla');
        });
    }
};
