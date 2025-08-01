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
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('raw_material_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->dropColumn('supplier');
        });
    }
};