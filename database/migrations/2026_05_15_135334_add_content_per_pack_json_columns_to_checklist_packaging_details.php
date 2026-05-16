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
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->json('content_per_pack_json')->nullable()->after('content_per_pack_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->dropColumn('content_per_pack_json');
        });
    }
};
