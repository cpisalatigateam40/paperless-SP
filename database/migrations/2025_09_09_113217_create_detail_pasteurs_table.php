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
        Schema::create('detail_pasteurs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid'); // FK ke report_pasteurs
            $table->uuid('product_uuid')->nullable();
            $table->string('program_number')->nullable();
            $table->string('product_code')->nullable();
            $table->decimal('for_packaging_gr', 10, 2)->nullable();
            $table->integer('trolley_count')->nullable();
            $table->decimal('product_temp', 8, 2)->nullable();
            $table->string('qc_paraf')->nullable();
            $table->string('production_paraf')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                  ->references('uuid')->on('report_pasteurs')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('detail_pasteurs');
    }
};