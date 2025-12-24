<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FragileItemTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'item_name',
            'section_name',
            'owner',
            'quantity',
        ];
    }

    public function array(): array
    {
        return [
            ['Contoh Barang', 'Contoh Section', 'QA', 5],
        ];
    }
}