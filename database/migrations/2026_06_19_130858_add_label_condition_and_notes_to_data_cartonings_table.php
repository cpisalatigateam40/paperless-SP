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
        Schema::table('data_cartonings', function (Blueprint $table) {
            $table->string('label_condition')
            ->nullable()
            ->after('carton_condition');

            $table->text('notes')
            ->nullable()
            ->after('label_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_cartonings', function (Blueprint $table) {
            $table->dropColumn([
                'label_condition',
                'notes',
            ]);
        });
    }
};
