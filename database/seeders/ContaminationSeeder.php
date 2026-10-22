<?php

namespace Database\Seeders;

use App\Models\ContaminationCategory;
use App\Models\ContaminationType;
use Illuminate\Database\Seeder;

class ContaminationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Hair' => ['Rambut'],
            'Plastic' => ['Plastic (soft)', 'Plastic (hard)', 'Sarung tangan'],
            'Metal' => ['Logam', 'Wiremesh', 'Titanium plate', 'Mur/baut', 'Alumunium foil', 'Isi staples'],
            'Paper' => ['Kertas'],
            'Glass' => ['Kaca', 'Teflon', 'Puing bangunan'],
            'Pest' => ['Lalat', 'Semut', 'Pest lainnya'],
            'Chicken Factors' => [
                'Tulang', 'Bungkil', 'Bulu', 'Yellow skin', 'By product',
                'Internal organ', 'Sisa pakan', 'Sekam', 'Grit', 'Darah beku',
            ],
            'Wood' => ['Kayu'],
            'Chemical' => ['Bahan kimia'],
            'Thread' => ['Benang', 'Kain'],
            'Rubber' => ['Karet'],
            'Product NG' => ['Produk tidak oke', 'Produk lain'],
            'Others' => ['Casing', 'Fiber', 'Oli & grease', 'Benda asing lainnya'],
        ];

        $categoryOrder = 1;

        foreach ($data as $categoryName => $types) {
            $category = ContaminationCategory::withTrashed()->updateOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $categoryOrder++, 'deleted_at' => null]
            );

            $typeOrder = 1;

            foreach ($types as $typeName) {
                ContaminationType::withTrashed()->updateOrCreate(
                    ['category_uuid' => $category->uuid, 'name' => $typeName],
                    ['sort_order' => $typeOrder++, 'deleted_at' => null]
                );
            }
        }
    }
}