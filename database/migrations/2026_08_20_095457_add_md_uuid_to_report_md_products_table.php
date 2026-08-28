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
        Schema::table('report_md_products', function (Blueprint $table) {
            $table->uuid('metal_detector_uuid')->nullable()->after('area_uuid');
            $table->foreign('metal_detector_uuid')->references('uuid')->on('metal_detectors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_md_products', function (Blueprint $table) {
            $table->dropForeign(['metal_detector_uuid']);
            $table->dropColumn('metal_detector_uuid');
        });
    }
};
