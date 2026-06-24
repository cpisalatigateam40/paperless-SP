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
        Schema::table('weight_stuffer_documentations', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weight_stuffer_documentations', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_id')->nullable(false)->change();
        });
    }
};
