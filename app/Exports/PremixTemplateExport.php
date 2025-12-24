<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PremixTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'producer',
            'shelf_life',
        ];
    }

    public function array(): array
    {
        return [
            ['Premix Contoh', 'PT Contoh', 12],
        ];
    }
}
