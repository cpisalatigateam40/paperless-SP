<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RawMaterialTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['material_name'];
    }

    public function array(): array
    {
        return [
            ['Material Contoh'],
        ];
    }
}
