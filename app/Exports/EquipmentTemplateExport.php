<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EquipmentTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['name', 'parts'];
    }

    public function array(): array
    {
        return [
            ['Mesin Giling', 'Pisau,Motor,Belt'],
        ];
    }
}
