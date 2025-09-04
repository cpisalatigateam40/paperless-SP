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
            $table->string('upload_md')->nullable();
            $table->string('upload_qr')->nullable();
            $table->string('upload_ed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_packaging_verifs', function (Blueprint $table) {
            $table->dropColumn('upload_md');
            $table->dropColumn('upload_qr');
            $table->dropColumn('upload_ed');
        });
    }
};