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
        Schema::table('data_freezings', function (Blueprint $table) {
            $table->string('iqf_machine')->nullable()->after('detail_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_freezings', function (Blueprint $table) {
            $table->dropColumn('iqf_machine');
        });
    }
};
