<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'product_name',
            'brand',
            'nett_weight',
            'shelf_life',
        ];
    }

    public function array(): array
    {
        return [
            ['Produk Contoh', 'Brand Contoh', 250, 12],
        ];
    }
}
