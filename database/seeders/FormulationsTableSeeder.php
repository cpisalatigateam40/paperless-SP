<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FormulationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.  
     */
    public function run(): void
    {
        $formulas = DB::table('formulas')->get()->keyBy('formula_name');
        $rawMaterials = DB::table('raw_materials')->pluck('uuid', 'material_name')->toArray();
        $premixes = DB::table('premixes')->pluck('uuid', 'name')->toArray();

        $details = [

            // Champ Chicken Ball (K) - Formula 20
            [
                'formula_name' => '20',
                'raw_materials' => ['BB', 'BL'],
                'weights_raw' => [100, 200],
                'premixes' => ['Ice', 'N SP 100CBC3', 'N MP 100CBC3', 'Bawang Merah', 'Bawang Putih', 'Skin Emulsion', 'Tapioka (TSN)', 'N O 100CBC1'],
                'weights_premix' => [10, 5, 5, 3, 3, 2, 15, 1],
            ],

            // Champ Chicken Ball (K) - Formula 31
            [
                'formula_name' => '31',
                'raw_materials' => ['SBB/Fillet/DPSBB', 'SBL/DPSBL', 'Skin neck'],
                'weights_raw' => [150, 100, 50],
                'premixes' => ['Ice', 'N SP 100CBC3', 'N MP 100CBC3', 'Bawang Merah', 'Bawang Putih', 'Skin Emulsion', 'ISP B', 'Water', 'Tapioka (TSN)', 'FBR A', 'N O 100CBC1'],
                'weights_premix' => [10, 5, 5, 3, 3, 2, 2, 20, 15, 1, 1],
            ],

            // Champ (K) - Formula 6
            [
                'formula_name' => '6',
                'raw_materials' => ['BB', 'BL', 'CCM', 'Skin/Skin neck', 'FAT CARCAS'],
                'weights_raw' => [120, 100, 80, 50, 30],
                'premixes' => ['Palm oil', 'Water', 'Ice', 'ISP B', 'N SP 495CSC5 A', 'A SP 495CSC5 B', 'N MP 495 CSC5', 'Wilcon SJ/ SPC', 'N O 990CSC', 'Tapioka (TSN)', 'N 160CSBK1 A', 'A TG 160CSBK', 'Caramel color', 'N 160CSBK1 B', 'A 160CSBK1 C', 'FL B (frankfurter flavor)', 'N P 160CSBK', 'N O 160CSBK'],
                'weights_premix' => [10, 20, 5, 2, 1, 1, 1, 1, 0.5, 5, 0.5, 0.5, 0.3, 0.5, 0.5, 0.1, 0.5, 0.5],
            ],

            // Champ (K) - Formula 9
            [
                'formula_name' => '9',
                'raw_materials' => ['SBB/Fillet/DPSBB', 'SBL/DPSBL', 'CCM', 'MDM Griller Mix/MDM NY', 'Skin/Skin neck'],
                'weights_raw' => [150, 120, 80, 50, 30],
                'premixes' => ['Palm oil', 'Water', 'Ice', 'ISP B', 'FBR A', 'N SP 495CSC5 A', 'A SP 495CSC5 B', 'N MP 495 CSC5', 'Wilcon SJ/ SPC', 'N O 990CSC', 'Tapioka (TSN)'],
                'weights_premix' => [10, 20, 5, 2, 1, 1, 1, 1, 1, 0.5, 5],
            ],

            // Champ (K) - Formula 10
            [
                'formula_name' => '10',
                'raw_materials' => ['SBB/Fillet/DPSBB', 'SBL/DPSBL', 'CCM', 'Skin/Skin neck'],
                'weights_raw' => [150, 120, 80, 30],
                'premixes' => ['Water', 'Ice', 'Wilcon SJ/ SPC', 'ISP B', 'Tapioka (TSN)', 'N SP 495CSC5 A', 'A SP 495CSC5 B', 'N MP 495 CSC5', 'N O 990CSC'],
                'weights_premix' => [20, 5, 1, 2, 5, 1, 1, 1, 0.5],
            ],

            // Champ (K) - Formula 13
            [
                'formula_name' => '13',
                'raw_materials' => ['SBB/Fillet/DPSBB', 'SBL/DPSBL', 'CCM', 'Skin/Skin neck', 'JANTUNG'],
                'weights_raw' => [150, 120, 80, 30, 20],
                'premixes' => ['Palm oil', 'Water', 'Ice', 'ISP B', 'FBR A', 'N SP 495CSC5 A', 'A SP 495CSC5 B', 'N MP 495 CSC5', 'Wilcon SJ/ SPC', 'N O 990CSC', 'Tapioka (TSN)'],
                'weights_premix' => [10, 20, 5, 2, 1, 1, 1, 1, 1, 0.5, 5],
            ],

            // Champ (K) - Formula 15
            [
                'formula_name' => '15',
                'raw_materials' => ['SBB/Fillet/DPSBB', 'SBL/DPSBL', 'CCM', 'Skin/Skin neck', 'FAT CARCAS'],
                'weights_raw' => [150, 120, 80, 30, 20],
                'premixes' => ['Palm oil', 'Water', 'Ice', 'Vege 860 (FBR A)', 'Wilcon SJ/ SPC', 'ISP B', 'Tapioka (TSN)', 'N SP 495CSC5 A', 'A SP 495CSC5 B', 'N MP 495 CSC5', 'N O 990CSC'],
                'weights_premix' => [10, 20, 5, 1, 1, 2, 5, 1, 1, 1, 0.5],
            ],

            // Beef (K) - Formula 11
            [
                'formula_name' => '11',
                'raw_materials' => ['BEEF C185 minced', 'SKIN NECK', 'CCM'],
                'weights_raw' => [200, 50, 30],
                'premixes' => ['Ice', 'N SP 200BSC9', 'N P 200BSC', 'N MP 200BSC9', 'ISP A', 'Oil Emulsion', 'Potato Starch (POT)', 'N O 200BSC', 'Tapioka (TSN)'],
                'weights_premix' => [5, 1, 1, 1, 1, 2, 5, 0.5, 5],
            ],

            [
                // Beef (K) - Formula 12
                'formula_name' => '12',
                'raw_materials' => ['BEEF C185 minced', 'SKIN NECK', 'CCM', 'JANTUNG'],
                'weights_raw' => [200, 50, 30, 20],
                'premixes' => ['Ice', 'N SP 200BSC9', 'N P 200BSC', 'N MP 200BSC9', 'ISP A', 'Oil Emulsion', 'Potato Starch (POT)', 'N O 200BSC', 'Tapioka (TSN)'],
                'weights_premix' => [5, 1, 1, 1, 1, 2, 5, 0.5, 5],
            ],

            [
                // Asimo (K) - Formula 3
                'formula_name' => '3',
                'raw_materials' => ['CCM'],
                'weights_raw' => [200],
                'premixes' => [
                    'Emulsi Gel ISP Putih',
                    'Palm oil',
                    'Tapioka (TSN)',
                    'Bawang Putih',
                    'Ice',
                    'Water',
                    'Wilcon SJ/ SPC',
                    'Corn Starch (COS)',
                    'Tapbind 365 (MTP A)',
                    'Vege 860 (FBR A)',
                    'N SP 320ASK2 A',
                    'A SP 320ASK2 B',
                    'N P 960 ASK',
                    'N O 960 ASK1',
                    'N MP 320ASK2',
                    'Paprika Oil (PAO A)',
                    'EMES (MPS)'
                ],
                'weights_premix' => [2, 10, 15, 3, 5, 20, 1, 5, 1, 1, 0.5, 0.5, 0.5, 0.5, 0.5, 0.2, 0.1],
            ],

            [
                // Asimo (K) - Formula 9
                'formula_name' => '9',
                'raw_materials' => ['CCM', 'Skin neck'],
                'weights_raw' => [200, 50],
                'premixes' => [
                    'Emulsi Gel ISP Putih',
                    'Palm oil',
                    'Tapioka (TSN)',
                    'Bawang Putih',
                    'Ice',
                    'Water',
                    'Wilcon SJ/ SPC',
                    'Corn Starch (COS)',
                    'Tapbind 365 (MTP A)',
                    'Vege 860 (FBR A)',
                    'N SP 320ASK2 A',
                    'A SP 320ASK2 B',
                    'N P 960 ASK',
                    'N O 960 ASK1',
                    'N MP 320ASK2'
                ],
                'weights_premix' => [2, 10, 15, 3, 5, 20, 1, 5, 1, 1, 0.5, 0.5, 0.5, 0.5, 0.5],
            ],

            [
                // Okey (K) - Formula 76
                'formula_name' => '76',
                'raw_materials' => ['CCM', 'LIVER', 'SKIN NECK', 'SBL/DPSBL'],
                'weights_raw' => [100, 30, 20, 50],
                'premixes' => [
                    'Bawang Putih',
                    'TSP',
                    'Water',
                    'Ice',
                    'N O 800CS0',
                    'N P 800CSO',
                    'N MP 400CSO10',
                    'N SP 400CSO10 A',
                    'N SP 400CSO10 B',
                    'Tepung Beras (TBR)',
                    'MTP B',
                    'MTP A',
                    'MTP C',
                    'Tapioka (TSN)',
                    'Sagu (TSG)',
                    'Caramel Color (CRL B)'
                ],
                'weights_premix' => [3, 0.5, 20, 5, 0.5, 0.5, 0.5, 0.5, 0.5, 5, 1, 1, 1, 10, 5, 0.2],
            ],

            [
                // Okey (K) - Formula 84
                'formula_name' => '84',
                'raw_materials' => ['CCM', 'CCM BLOCK', 'MDM Griller Mix/MDM NY', 'LIVER', 'SKIN NECK'],
                'weights_raw' => [100, 50, 30, 20, 20],
                'premixes' => [
                    'Bawang Putih',
                    'TSP',
                    'Water',
                    'Ice',
                    'N O 800CS0',
                    'N P 800CSO',
                    'N MP 400CSO10',
                    'N SP 400CSO10 A',
                    'N SP 400CSO10 B',
                    'Wilcon SJ/ SPC',
                    'MTP B',
                    'MTP A',
                    'MTP C',
                    'Tapioka (TSN)',
                    'Sagu (TSG)',
                    'Corn Starch (COS)',
                    'Caramel Color (CRL B)',
                    'FBR A'
                ],
                'weights_premix' => [3, 0.5, 20, 5, 0.5, 0.5, 0.5, 0.5, 0.5, 1, 1, 1, 1, 10, 5, 5, 0.2, 0.5],
            ],

            [
                // Okey (K) - Formula 85
                'formula_name' => '85',
                'raw_materials' => ['CCM', 'CCM BLOCK', 'MDM Griller Mix/MDM NY', 'LIVER', 'SKIN NECK'],
                'weights_raw' => [100, 50, 30, 20, 20],
                'premixes' => [
                    'Palm oil',
                    'Bawang Putih',
                    'TSP',
                    'Water',
                    'Ice',
                    'N O 800CS0',
                    'N P 800CSO',
                    'N MP 400CSO10',
                    'N SP 400CSO10 A',
                    'N SP 400CSO10 B',
                    'Wilcon SJ/ SPC',
                    'MTP B',
                    'MTP A',
                    'MTP C',
                    'Tapioka (TSN)',
                    'Sagu (TSG)',
                    'Corn Starch (COS)',
                    'Caramel Color (CRL B)',
                    'FBR A'
                ],
                'weights_premix' => [10, 3, 0.5, 20, 5, 0.5, 0.5, 0.5, 0.5, 0.5, 1, 1, 1, 1, 10, 5, 5, 0.2, 0.5],
            ],

            [
                // Okey (K) - Formula 86
                'formula_name' => '86',
                'raw_materials' => ['CCM', 'LIVER', 'SKIN NECK'],
                'weights_raw' => [150, 30, 20],
                'premixes' => [
                    'Palm oil',
                    'Bawang Putih',
                    'TSP',
                    'Water',
                    'Ice',
                    'N O 800CS0',
                    'N P 800CSO',
                    'N MP 400CSO10',
                    'N SP 400CSO10 A',
                    'N SP 400CSO10 B',
                    'Tepung Beras (TBR)',
                    'MTP B',
                    'MTP A',
                    'MTP C',
                    'Tapioka (TSN)',
                    'Sagu (TSG)',
                    'Caramel Color (CRL B)',
                    'FBR A',
                    'ISP B'
                ],
                'weights_premix' => [10, 3, 0.5, 20, 5, 0.5, 0.5, 0.5, 0.5, 0.5, 5, 1, 1, 1, 10, 5, 0.2, 0.5, 0.5],
            ],

            [
                // Okey (K) - Formula 92
                'formula_name' => '92',
                'raw_materials' => ['CCM BLOCK', 'MDM Griller Mix/MDM NY', 'SKIN NECK', 'LIVER'],
                'weights_raw' => [100, 50, 20, 20],
                'premixes' => [
                    'Palm oil',
                    'Bawang Putih',
                    'TSP',
                    'Water',
                    'Ice',
                    'N O 800CS0',
                    'N P 800CSO',
                    'N MP 400CSO10',
                    'N SP 400CSO10 A',
                    'N SP 400CSO10 B',
                    'Wilcon SJ/ SPC',
                    'MTP B',
                    'MTP A',
                    'MTP C',
                    'Tapioka (TSN)',
                    'Sagu (TSG)',
                    'Caramel Color (CRL B)',
                    'FBR A',
                    'Corn Starch (COS)'
                ],
                'weights_premix' => [10, 3, 0.5, 20, 5, 0.5, 0.5, 0.5, 0.5, 0.5, 1, 1, 1, 1, 10, 5, 0.2, 0.5, 5],
            ],

            [
                // Fiesta - Formula 1
                'formula_name' => '1',
                'raw_materials' => ['SBB minced 5mm', 'SKIN minced 3mm'],
                'weights_raw' => [200, 50],
                'premixes' => [
                    'Ice',
                    'N 160CT',
                    'A 160CT',
                    'Corn Starch (COS)',
                    'Skin Emulsion',
                    'Minyak Goreng',
                    'NO 150 SCB',
                    'MTP A',
                    'MTP B',
                    'A 150 SCB1',
                    'N 150 SCB1',
                    'N 80CSA5 A',
                    'A 80CSA5 B',
                    'N O 80CSA'
                ],
                'weights_premix' => [5, 0.5, 0.5, 5, 2, 10, 0.5, 1, 1, 0.5, 0.5, 0.5, 0.5, 0.5],
            ],
        ];

        foreach ($details as $detail) {
            $formula_uuid = $formulas[$detail['formula_name']]->uuid ?? null;
            $formulationName = "Formulasi - {$detail['formula_name']}";

            // RAW MATERIALS
            foreach ($detail['raw_materials'] as $index => $material) {
                DB::table('formulations')->insert([
                    'uuid' => Str::uuid(),
                    'formula_uuid' => $formula_uuid,
                    'raw_material_uuid' => $rawMaterials[$material] ?? null,
                    'premix_uuid' => null,
                    'formulation_name' => $formulationName,
                    'weight' => $detail['weights_raw'][$index] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // PREMIXES
            foreach ($detail['premixes'] as $index => $premix) {
                DB::table('formulations')->insert([
                    'uuid' => Str::uuid(),
                    'formula_uuid' => $formula_uuid,
                    'raw_material_uuid' => null,
                    'premix_uuid' => $premixes[$premix] ?? null,
                    'formulation_name' => $formulationName,
                    'weight' => $detail['weights_premix'][$index] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

    }
}