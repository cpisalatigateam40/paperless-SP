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
        Schema::table('detail_mt_cleans', function (Blueprint $table) {
            $table->decimal('metal_weight', 10, 3)
                ->nullable()
                ->after('mt_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_mt_cleans', function (Blueprint $table) {
            $table->dropColumn('metal_weight');
        });
    }
};
