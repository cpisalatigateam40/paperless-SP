<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ThermometerTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['code', 'type', 'brand'];
    }

    public function array(): array
    {
        return [
            ['TH-01', 'Digital', 'Omron'],
        ];
    }
}
