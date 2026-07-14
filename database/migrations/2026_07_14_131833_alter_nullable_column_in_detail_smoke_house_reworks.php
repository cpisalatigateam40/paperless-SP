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
        Schema::table('detail_smoke_house_reworks', function (Blueprint $table) {
            $table->unsignedInteger('smoke_house_no')->nullable()->change();
            $table->unsignedInteger('trolley_count')->nullable()->change();
            $table->unsignedInteger('stick_count')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_smoke_house_reworks', function (Blueprint $table) {
            $table->unsignedInteger('smoke_house_no')->nullable(false)->change();
            $table->unsignedInteger('trolley_count')->nullable(false)->change();
            $table->unsignedInteger('stick_count')->nullable(false)->change();
        });
    }
};
