<?php

namespace Database\Seeders;

use App\Models\MasterChecklistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterChecklistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [

            'Chillroom' => [
                'Rak',
                'Pallet',
                'Pisau',
                'Mangkok Stainless',
                'Box Kuning',
                'Box Stainless',
                'Meatcar',
            ],

            'Seasoning' => [
                'Rak',
                'Pallet',
                'Pisau',
                'Mangkok Stainless',
                'Timbangan Meatcar',
                'Timbangan Seasoning',
                'Meatcar',
            ],

            'Meat Preparation & Cooking' => [
                'Pisau',
                'Meatcar',
                'Meja Sortir',
                'Mesin auto grind',
                'Mesin mixer',
                'Mesin bowl cutter',
                'Mesin Emulsifier',
                'Mesin sunny pump',
                'Mesin stuffer',
                'Timbangan stuffer',
                'Keranjang & saringan',
                'Trolley',
                'Stick Trolley',
                'Mesin Smokehouse (Maurer)',
                'Mesin Smokehouse (Fessman)',
            ],

            'Cooking Baso' => [
                'Mesin pencetak baso anco',
                'Mesin pencetak baso trifloat',
                'Bagian dalam mesin boiling 1 & 2',
                'Keranjang Merah',
                'Keranjang Hijau',
                'Meja Timbangan',
                'Timbangan baso',
            ],
        ];

        foreach ($data as $category => $items) {

            foreach ($items as $index => $item) {

                MasterChecklistItem::create([
                    'uuid'         => Str::uuid(),
                    'category'     => $category,
                    'name'         => $item,
                    'order_number' => $index + 1,
                    'is_active'    => true,
                ]);
            }
        }
    }
}