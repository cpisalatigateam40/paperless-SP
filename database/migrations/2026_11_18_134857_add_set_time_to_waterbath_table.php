<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['master_waterbath_parameters', 'run_parameter_waterbaths'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn('set_time', 'set_time_min');
            });

            Schema::table($name, function (Blueprint $table) {
                $table->decimal('set_time_max', 8, 2)->nullable()->after('set_time_min');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('set_time_max');
            });

            Schema::table($name, function (Blueprint $table) {
                $table->renameColumn('set_time_min', 'set_time');
            });
        }
    }
};