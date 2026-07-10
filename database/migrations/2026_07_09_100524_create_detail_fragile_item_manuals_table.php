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
        Schema::create('detail_fragile_item_manuals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('report_fragile_item_uuid');
            $table->uuid('section_uuid')->nullable();
            $table->string('sub_area')->nullable();
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('condition')->nullable();
            $table->string('employee_name')->nullable();
            $table->text('issue_notes')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();

            $table->foreign('report_fragile_item_uuid')->references('uuid')->on('report_fragile_items')->onDelete('cascade');
            $table->foreign('section_uuid')->references('uuid')->on('sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_fragile_item_manuals');
    }
};
