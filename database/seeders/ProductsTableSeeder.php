<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_name' => 'Champ Chicken Ball (K)',
                'brand' => 'Champ',
                'nett_weight' => 104.504,
            ],
            [
                'product_name' => 'Champ Sosis Bakar Jumbo 500gr',
                'brand' => 'Champ',
                'nett_weight' => 160.010,
            ],
            [
                'product_name' => 'Beef (K)',
                'brand' => 'Beef',
                'nett_weight' => 863.782,
            ],
            [
                'product_name' => 'Champ (K)',
                'brand' => 'Champ',
                'nett_weight' => 991.064, // Salah satu formula sebagai contoh
            ],
            [
                'product_name' => 'Okey (K)',
                'brand' => 'Okey',
                'nett_weight' => 809.177, // Salah satu formula sebagai contoh
            ],
            [
                'product_name' => 'Asimo (K)',
                'brand' => 'Asimo',
                'nett_weight' => 925.916,
            ],
            [
                'product_name' => 'Fiesta Chicken TOFU 500gr',
                'brand' => 'Fiesta',
                'nett_weight' => 200.048,
            ],
            [
                'product_name' => 'Fiesta',
                'brand' => 'Fiesta',
                'nett_weight' => 170.796,
            ],
            [
                'product_name' => 'Umbul Sidomukti Chicken Sausage 1KG',
                'brand' => 'Umbul Sidomukti',
                'nett_weight' => 170.796,
            ],
            [
                'product_name' => 'FIESTA SCB 250gr',
                'brand' => 'Fiesta',
                'nett_weight' => 197.088,
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'uuid' => Str::uuid(),
                'product_name' => $product['product_name'],
                'brand' => $product['brand'],
                'nett_weight' => $product['nett_weight'],
                'shelf_life' => 12,
                'area_uuid' => '921912ed-da38-4bfc-b041-90d7c10c5dd4',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}