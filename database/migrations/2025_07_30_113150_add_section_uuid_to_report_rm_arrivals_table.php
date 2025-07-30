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
        Schema::table('report_rm_arrivals', function (Blueprint $table) {
            $table->uuid('section_uuid')->nullable()->after('area_uuid');

            $table->foreign('section_uuid')
                ->references('uuid')
                ->on('sections')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_rm_arrivals', function (Blueprint $table) {
            $table->dropForeign(['section_uuid']);
            $table->dropColumn('section_uuid');
        });
    }
};