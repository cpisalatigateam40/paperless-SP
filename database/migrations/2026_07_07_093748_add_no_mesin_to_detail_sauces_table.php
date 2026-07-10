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
            $table->string('no_mesin')->nullable()->after('process_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_sauces', function (Blueprint $table) {
            $table->dropColumn('no_mesin');
        });
    }
};
