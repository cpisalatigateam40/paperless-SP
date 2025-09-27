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
            $table->string('standard_weight')->change();
            $table->string('standard_long_pcs')->change();
            $table->string('standard_weight_pcs')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->decimal('standard_weight', 8, 2)->change();
            $table->decimal('standard_long_pcs', 8, 2)->change();
            $table->decimal('standard_weight_pcs', 8, 2)->change();
        });
    }
};