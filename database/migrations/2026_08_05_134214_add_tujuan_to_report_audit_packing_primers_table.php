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
        Schema::table('report_audit_packing_primers', function (Blueprint $table) {
            $table->text('tujuan')->nullable()->after('section_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_audit_packing_primers', function (Blueprint $table) {
            $table->dropColumn('tujuan');
        });
    }
};
