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
        Schema::table('report_waterbaths', function (Blueprint $table) {
            // 1 = form lama, 2 = form master data (all area), 3 = Cikande 1 (nanti)
            $table->unsignedTinyInteger('form_version')->default(1)->after('shift');

            // Hanya dipakai form versi 2+ (form lama pakai detail_waterbaths)
            $table->uuid('product_uuid')->nullable()->after('form_version')->index();
            $table->decimal('gramase', 8, 2)->nullable()->after('product_uuid');

            $table->index('form_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_waterbaths', function (Blueprint $table) {
            $table->dropIndex(['form_version']);
            $table->dropIndex(['product_uuid']);
            $table->dropColumn(['form_version', 'product_uuid', 'gramase']);
        });
    }
};
