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
        Schema::table('detail_freez_packagings', function (Blueprint $table) {
             $table->string('corrective_action')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_freez_packagings', function (Blueprint $table) {
            $table->dropColumn('corrective_action');
        });
    }
};