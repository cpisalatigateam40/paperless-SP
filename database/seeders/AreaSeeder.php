<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            'Salatiga',
            'Medan',
        ];

        foreach ($areas as $areaName) {
            Area::firstOrCreate(
                ['name' => $areaName],
                ['uuid' => (string) Str::uuid()]
            );
        }
    }
}