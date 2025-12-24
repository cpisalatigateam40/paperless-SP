<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScaleTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['code', 'type', 'brand', 'owner'];
    }

    public function array(): array
    {
        return [
            ['SC-01', 'Digital', 'Ohaus', 'QA'],
        ];
    }
}
