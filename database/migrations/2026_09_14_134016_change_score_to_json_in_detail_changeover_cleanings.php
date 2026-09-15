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
        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->json('score_new')->nullable()->after('score');
        });

        DB::table('detail_changeover_cleanings')
            ->whereNotNull('score')
            ->orderBy('uuid')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('detail_changeover_cleanings')
                        ->where('uuid', $row->uuid)
                        ->update(['score_new' => json_encode([(int) $row->score])]);
                }
            }, 'uuid');

        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->renameColumn('score_new', 'score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->integer('score')->nullable()->change();
        });
    }
};
