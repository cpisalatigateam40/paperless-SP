<?php

namespace Database\Seeders;

use App\Models\MasterAuditPackingPrimerItem;
use Illuminate\Database\Seeder;

class MasterAuditPackingPrimerItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'food_safety' => [
                'Tidak ditemukan potensi kontaminasi benda asing pada produk maupun area kerja (rambut, logam, plastik, bulu, pest).',
                'Operator & karyawan packing menggunakan APD sesuai standar dan menerapkan GMP selama proses packing.',
                'Mesin, conveyor, dan area packing dalam kondisi bersih serta bebas material yang berpotensi menjadi kontaminan.',
            ],
            'food_quality' => [
                'Tidak ditemukan produk isi kurang atau produk kosong (empty pack) selama observasi.',
                'Tidak ditemukan defect produk (sosis pecah, sosis keriput, bakso ukuran tidak seragam, bakso tanpa isi, dan lainnya).',
                'Tidak ditemukan kesalahan isi (wrong product) maupun saus kurang sesuai standar produk.',
                'Tidak ditemukan produk yang tidak terbungkus kemasan dengan benar, kemasan loss vacuum, atau kemasan keriput.',
            ],
            'process_compliance' => [
                'Operator melakukan penyortiran produk tidak sesuai SOP dan petugas sortir berada pada pos inspeksi selama produksi.',
                'Metal Detector, checkweigher dan rejector berfungsi normal serta seluruh produk reject dipisahkan sesuai prosedur.',
                'Tidak ditemukan kondisi abnormal yang berpotensi menyebabkan ketidaksesuaian produk.',
            ],
        ];

        foreach ($items as $category => $questions) {
            foreach ($questions as $index => $question) {
                MasterAuditPackingPrimerItem::updateOrCreate(
                    [
                        'category' => $category,
                        'item_number' => $index + 1,
                    ],
                    [
                        'item_verifikasi' => $question,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}