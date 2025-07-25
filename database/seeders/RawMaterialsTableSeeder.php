<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RawMaterialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            'BB',
            'BL',
            'CCM',
            'BEEF C185 minced',
            'SKIN NECK',
            'SBB/Fillet/DPSBB',
            'SBL/DPSBL',
            'Skin neck',
            'Skin/Skin neck',
            'FAT CARCAS',
            'MDM Griller Mix/MDM NY',
            'CCM BLOCK',
            'SKIN',
            'SBB minced 5mm',
            'SKIN minced 3mm',
            'BB Utuh',
            'JANTUNG',
            'LIVER',
        ];

        foreach ($materials as $material) {
            DB::table('raw_materials')->insert([
                'uuid' => Str::uuid(),
                'material_name' => $material,
                'supplier' => 'produsen test',
                'shelf_life' => 12,
                'area_uuid' => '921912ed-da38-4bfc-b041-90d7c10c5dd4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}