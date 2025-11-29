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
        Schema::table('detail_weight_stuffers', function (Blueprint $table) {
            $table->string('weight_standard')->nullable()->change();
            $table->string('long_standard')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_weight_stuffers', function (Blueprint $table) {
            $table->double('weight_standard')->nullable()->change();
            $table->double('long_standard')->nullable()->change();
        });
    }
};
