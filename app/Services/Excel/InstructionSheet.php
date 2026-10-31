<?php

namespace App\Services\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

/* Sheet 3: Instruksi */
class InstructionSheet implements FromArray, WithTitle
{
    public function __construct(
        private array $mapping,
        private array $masterFields,
        private array $context = []
    ) {}

    public function title(): string
    {
        return 'Instruksi';
    }

    public function array(): array
    {
        $result = [
            ['=== PANDUAN IMPORT DATA ==='],
            [''],
            ['PETUNJUK UMUM:'],
            ['1. Kolom bertanda (*) wajib diisi'],
            ['2. Gunakan dropdown bila tersedia (jangan ketik manual)'],
            ['3. Jangan mengubah atau menghapus header (baris pertama)'],
            ['4. Jangan mengubah sheet "Master Data"'],
            ['5. Isi data mulai dari baris ke-2 di sheet "Data Import"'],
            [''],
            ['FORMAT DATA:'],
        ];

        // baris format hanya muncul bila kolomnya memang ada di template
        if (in_array('date', $this->mapping, true)) {
            $result[] = ['- Tanggal: YYYY-MM-DD (contoh: ' . now()->format('Y-m-d') . ')'];
        }
        $result[] = ['- Angka desimal: gunakan titik (contoh: 4.5)'];
        $result[] = [''];

        // nilai yang diisi otomatis oleh sistem, TIDAK ada kolomnya di template
        if (!empty($this->context)) {
            $result[] = ['INFORMASI OTOMATIS (tidak perlu diisi):'];
            foreach ($this->context as $label => $value) {
                $result[] = ['- ' . $label . ': ' . $value];
            }
            $result[] = [''];
        }

        $result[] = ['KOLOM YANG TERSEDIA:'];
        foreach ($this->mapping as $excelCol => $dbField) {
            $dropdown = isset($this->masterFields[$dbField]) ? ' (Dropdown)' : '';
            $result[] = ['- ' . $excelCol . $dropdown];
        }

        return $result;
    }
}