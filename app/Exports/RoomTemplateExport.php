<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoomTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['name', 'elements'];
    }

    public function array(): array
    {
        return [
            ['Ruang Produksi', 'Lantai,Dinding,Langit-langit'],
        ];
    }
}
