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
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->string('standard_weight')->nullable()->change();
            $table->string('standard_long_pcs')->nullable()->change();
            $table->string('standard_weight_pcs')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->string('standard_weight')->nullable(false)->change();
            $table->string('standard_long_pcs')->nullable(false)->change();
            $table->string('standard_weight_pcs')->nullable(false)->change();
        });
    }
};
