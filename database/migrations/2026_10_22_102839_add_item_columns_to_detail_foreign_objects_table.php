<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            $table->string('item_type', 20)->default('product')->after('report_uuid');
            $table->uuid('item_uuid')->nullable()->after('item_type');
            $table->index('item_uuid');

            // Raw material / premix tidak punya product_uuid
            $table->uuid('product_uuid')->nullable()->change();
        });

        // Backfill data lama: semuanya berasal dari produk
        DB::table('detail_foreign_objects')
            ->whereNotNull('product_uuid')
            ->update(['item_uuid' => DB::raw('product_uuid')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            $table->dropIndex(['item_uuid']);
            $table->dropColumn(['item_type', 'item_uuid']);
        });
    }
};
