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
        Schema::create('master_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->string('category')->nullable(); // misal: "Packing"
            $table->string('name'); // misal: "Konveyer filling 1 & meja"
            $table->integer('order_number')->nullable(); // urutan tampil (kolom NO)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
 
            $table->foreign('area_uuid')
                ->references('uuid')
                ->on('areas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_checklist_items');
    }
};
