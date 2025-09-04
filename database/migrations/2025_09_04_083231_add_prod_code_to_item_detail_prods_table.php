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
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->string('prod_code')->nullable()->after('sensory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->dropColumn('prod_code');
        });
    }
};