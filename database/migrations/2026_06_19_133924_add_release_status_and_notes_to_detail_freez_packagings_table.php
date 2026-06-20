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
            $table->string('release_status')
            ->nullable()
            ->after('gramase');

            $table->text('notes')
                ->nullable()
                ->after('release_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_freez_packagings', function (Blueprint $table) {
            $table->dropColumn([
                'release_status',
                'notes'
            ]);
        });
    }
};
