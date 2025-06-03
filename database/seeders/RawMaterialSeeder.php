<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RawMaterial;
use Illuminate\Support\Str;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areaUuids = \App\Models\Area::pluck('uuid')->toArray();

        for ($i = 1; $i <= 10; $i++) {
            RawMaterial::create([
                'uuid' => Str::uuid(),
                'material_name' => 'Material ' . $i,
                'production_code' => 'PRD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'area_uuid' => $areaUuids[array_rand($areaUuids)] ?? null,
            ]);
        }
    }
}