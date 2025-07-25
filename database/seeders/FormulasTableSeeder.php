<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FormulasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productMap = DB::table('products')->pluck('uuid', 'product_name')->toArray();

        $formulas = [
            [
                'product_name' => 'Champ Chicken Ball (K)',
                'formula_name' => '20',
            ],
            [
                'product_name' => 'Champ Chicken Ball (K)',
                'formula_name' => '31',
            ],
            [
                'product_name' => 'Champ (K)',
                'formula_name' => '6',
            ],
            [
                'product_name' => 'Champ (K)',
                'formula_name' => '9',
            ],
            [
                'product_name' => 'Champ (K)',
                'formula_name' => '10',
            ],
            [
                'product_name' => 'Champ (K)',
                'formula_name' => '13',
            ],
            [
                'product_name' => 'Champ (K)',
                'formula_name' => '15',
            ],
            [
                'product_name' => 'Beef (K)',
                'formula_name' => '11',
            ],
            [
                'product_name' => 'Beef (K)',
                'formula_name' => '12',
            ],
            [
                'product_name' => 'Asimo (K)',
                'formula_name' => '3',
            ],
            [
                'product_name' => 'Asimo (K)',
                'formula_name' => '9',
            ],
            [
                'product_name' => 'Okey (K)',
                'formula_name' => '76',
            ],
            [
                'product_name' => 'Okey (K)',
                'formula_name' => '84',
            ],
            [
                'product_name' => 'Okey (K)',
                'formula_name' => '85',
            ],
            [
                'product_name' => 'Okey (K)',
                'formula_name' => '86',
            ],
            [
                'product_name' => 'Okey (K)',
                'formula_name' => '92',
            ],
            [
                'product_name' => 'Champ Sosis Bakar Jumbo 500gr',
                'formula_name' => '1',
            ],
            [
                'product_name' => 'Fiesta Chicken TOFU 500gr',
                'formula_name' => '1',
            ],
            [
                'product_name' => 'Fiesta',
                'formula_name' => '1',
            ],
            [
                'product_name' => 'FIESTA SCB 250gr',
                'formula_name' => '1',
            ],
            [
                'product_name' => 'Umbul Sidomukti Chicken Sausage 1KG',
                'formula_name' => '1',
            ],
        ];

        foreach ($formulas as $formula) {
            DB::table('formulas')->insert([
                'uuid' => Str::uuid(),
                'product_uuid' => $productMap[$formula['product_name']] ?? null,
                'formula_name' => $formula['formula_name'],
                'area_uuid' => "921912ed-da38-4bfc-b041-90d7c10c5dd4",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}