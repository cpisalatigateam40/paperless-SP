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
        Schema::table('master_smoke_houses', function (Blueprint $table) {
            // buat index pengganti dulu, biar foreign key area_uuid tetap ada index-nya
            $table->index('area_uuid', 'master_smoke_houses_area_uuid_index');
        });

        Schema::table('master_smoke_houses', function (Blueprint $table) {
            // baru sekarang aman untuk drop unique constraint-nya
            $table->dropUnique('master_smoke_house_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_smoke_houses', function (Blueprint $table) {
            $table->unique(
                ['area_uuid', 'product_uuid', 'machine_name'],
                'master_smoke_house_unique'
            );
        });

        Schema::table('master_smoke_houses', function (Blueprint $table) {
            $table->dropIndex('master_smoke_houses_area_uuid_index');
        });
    }
};
