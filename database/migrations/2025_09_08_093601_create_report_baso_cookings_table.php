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
        Schema::create('report_baso_cookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('area_uuid')->nullable();
            $table->date('date')->nullable();
            $table->string('shift')->nullable();
            $table->uuid('product_uuid')->nullable();

            $table->string('std_core_temp')->nullable();
            $table->string('std_weight')->nullable();
            $table->float('set_boiling_1')->nullable();
            $table->float('set_boiling_2')->nullable();

            $table->string('created_by')->nullable();
            $table->string('known_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('area_uuid')
                ->references('uuid')
                ->on('areas')
                ->onDelete('set null');

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_baso_cookings');
    }
};