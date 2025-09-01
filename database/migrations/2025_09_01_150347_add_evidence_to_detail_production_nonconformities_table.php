<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_production_nonconformities', function (Blueprint $table) {
            $table->string('evidence')
            ->nullable()
            ->after('disposition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_production_nonconformities', function (Blueprint $table) {
            $table->dropColumn('evidence');
        });
    }
};