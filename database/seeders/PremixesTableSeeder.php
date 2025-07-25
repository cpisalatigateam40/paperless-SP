<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PremixesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $premixes = [
            'Ice',
            'N SP 100CBC3',
            'N MP 100CBC3',
            'Bawang Merah',
            'Bawang Putih',
            'Skin Emulsion',
            'Tapioka (TSN)',
            'N O 100CBC1',
            'N 160CSBK1 A',
            'A TG 160CSBK',
            'Palm oil',
            'Emulsi gel',
            'Caramel color',
            'Tapbind 365 (MTP A)',
            'Vege 860 (FBR A)',
            'FBR A',
            'N 160CSBK1 B',
            'A 160CSBK1 C',
            'FL B (frankfurter flavor)',
            'N P 160CSBK',
            'N O 160CSBK',
            'TSP',
            'N O 800CS0',
            'N P 800CSO',
            'N MP 400CSO10',
            'N SP 400CSO10 A',
            'N SP 400CSO10 B',
            'Tepung Beras (TBR)',
            'MTP B',
            'MTP A',
            'MTP C',
            'Sagu (TSG)',
            'Caramel Color (CRL B)',
            'ISP B',
            'ISP A',
            'Wilcon SJ/ SPC',
            'Potato Starch (POT)',
            'Corn Starch (COS)',
            'Oil Emulsion',
            'Paprika Oil (PAO A)',
            'EMES (MPS)',
            'Water',
            'Emulsi Gel ISP Putih',
            'N SP 320ASK2 A',
            'A SP 320ASK2 B',
            'N P 960 ASK',
            'N O 960 ASK1',
            'N MP 320ASK2',
            'N 160CT',
            'A 160CT',
            'Minyak Goreng',
            'A 150 SCB1',
            'N 150 SCB1',
            'NO 150 SCB',
            'N 80CSA5 A',
            'A 80CSA5 B',
            'N O 80CSA',
            // ✅ Tambahan yang wajib
            'N SP 495CSC5 A',
            'A SP 495CSC5 B',
            'N O 990CSC',
            'N SP 200BSC9',
            'N P 200BSC',
            'N MP 200BSC9',
            'N O 200BSC',
        ];


        foreach ($premixes as $premix) {
            DB::table('premixes')->insert([
                'uuid' => Str::uuid(),
                'name' => $premix,
                'production_code' => 'test-code',
                'shelf_life' => 12,
                'area_uuid' => '921912ed-da38-4bfc-b041-90d7c10c5dd4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}