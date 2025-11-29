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
        Schema::table('detail_packaging_verifs', function (Blueprint $table) {
            $table->json('upload_md_multi')->nullable()->after('upload_md');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_packaging_verifs', function (Blueprint $table) {
            $table->dropColumn(['upload_md_multi']);
        });
    }
};
