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
        Schema::create('detail_returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('report_uuid')->nullable();
            $table->uuid('rm_uuid')->nullable();
            $table->string('supplier')->nullable();
            $table->string('production_code')->nullable();
            $table->text('hold_reason')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->string('action')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_returns')
                ->onDelete('cascade');
            $table->foreign('rm_uuid')->references('uuid')->on('raw_materials')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_returns');
    }
};