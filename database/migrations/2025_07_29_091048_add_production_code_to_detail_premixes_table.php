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
        Schema::table('detail_premixes', function (Blueprint $table) {
             $table->string('production_code')->nullable()->after('premix_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_premixes', function (Blueprint $table) {
            $table->dropColumn('production_code');
        });
    }
};