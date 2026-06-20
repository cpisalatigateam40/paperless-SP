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
        Schema::create('detail_changeover_cleanings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid'); // FK ke report_changeover_cleanings
            $table->uuid('item_uuid');   // FK ke master_checklist_items
            $table->uuid('product_uuid')->nullable(); // FK ke products ("Nama Produk")
            $table->time('time')->nullable();         // Jam
            $table->string('result')->nullable();     // OK / Tidak OK
            $table->text('explanation')->nullable();  // Penjelasan (per cek)
            $table->text('notes')->nullable();         // Keterangan
            $table->text('corrective_action')->nullable(); // Tindakan Koreksi
            $table->timestamps();
 
            $table->foreign('report_uuid')
                  ->references('uuid')->on('report_changeover_cleanings')
                  ->onDelete('cascade');
 
            $table->foreign('item_uuid')
                  ->references('uuid')->on('master_checklist_items')
                  ->onDelete('restrict');
 
            $table->foreign('product_uuid')
                  ->references('uuid')->on('products')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_changeover_cleanings');
    }
};
