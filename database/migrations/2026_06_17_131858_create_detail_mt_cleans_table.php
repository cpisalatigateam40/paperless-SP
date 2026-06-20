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
        Schema::create('detail_mt_cleans', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->uuid('report_uuid');
            $table->uuid('product_uuid')->nullable();

            $table->time('time')->nullable();

            $table->string('mt_1')->nullable();
            $table->string('mt_2')->nullable();

            $table->string('finding_type')->nullable();
            $table->string('condition')->nullable();

            $table->text('note')->nullable();
            $table->text('corrective_action')->nullable();

            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_mt_cleans')
                ->onDelete('cascade');

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
        Schema::dropIfExists('detail_mt_cleans');
    }
};
