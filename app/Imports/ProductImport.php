<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class ProductImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['product_name'])) {
            return;
        }

        Product::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'product_name' => $data['product_name'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'brand' => $data['brand'] ?? null,
                'nett_weight' => $data['nett_weight'] ?? null,
                'shelf_life' => $data['shelf_life'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'nett_weight' => 'nullable|numeric|min:0',
            'shelf_life' => 'nullable|integer|min:0',
        ];
    }
}
