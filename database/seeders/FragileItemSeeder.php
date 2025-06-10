<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FragileItem;
use Illuminate\Support\Str;

class FragileItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['Lamp + Cover', 'Corridor MP', 'Produksi', 6],
            ['Acrylic Door', 'Corridor MP', 'Produksi', 1],
            ['Mirror Glass', 'Corridor MP', 'Produksi', 1],
            ['Fly Catcher Lamp', 'Corridor MP', 'Produksi', 1],
            ['Soap Dispenser', 'Corridor MP', 'QC', 4],
            ['Air Shower Glass', 'Corridor MP', 'Produksi', 10],
            ['Spray Bottle', 'Corridor MP', 'QC', 1],
            ['Smoke House Panel Cover', 'Cooking Sausage', 'Produksi', 4],
            ['Testo Thermometer 105 & 106', 'Cooking Sausage', 'QC', 4],
            ['Refractometer', 'Meatprep', 'Produksi', 1],
        ];

        foreach ($data as [$itemName, $sectionName, $owner, $quantity]) {
            FragileItem::create([
                'uuid' => Str::uuid(),
                'item_name' => $itemName,
                'section_name' => $sectionName,
                'owner' => $owner,
                'quantity' => $quantity,
                'area_uuid' => null,
            ]);
        }
    }
}