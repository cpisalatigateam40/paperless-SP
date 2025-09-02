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
        Schema::table('weight_stuffers', function (Blueprint $table) {
            $table->float('actual_long_1')
                ->nullable()
                ->after('actual_weight_3');
            $table->float('actual_long_2')
                ->nullable()
                ->after('actual_long_1');
            $table->float('actual_long_3')
                ->nullable()
                ->after('actual_long_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weight_stuffers', function (Blueprint $table) {
            $table->dropColumn('actual_long_1');
            $table->dropColumn('actual_long_2');
            $table->dropColumn('actual_long_3');
        });
    }
};