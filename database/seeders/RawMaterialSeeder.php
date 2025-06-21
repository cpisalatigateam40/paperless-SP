<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RawMaterial;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areaUuids = \App\Models\Area::pluck('uuid')->toArray();

        $suppliers = ['PT Indofood', 'PT Mayora', 'PT Wings', 'PT Unilever', 'PT Garudafood'];

        for ($i = 1; $i <= 10; $i++) {
            RawMaterial::create([
                'uuid' => Str::uuid(),
                'material_name' => 'Material ' . $i,
                'supplier' => $suppliers[array_rand($suppliers)],
                'shelf_life' => rand(1, 12),
                'area_uuid' => $areaUuids[array_rand($areaUuids)] ?? null,
            ]);
        }
    }
}