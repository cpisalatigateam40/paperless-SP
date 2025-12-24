<?php

namespace App\Imports;

use App\Models\RawMaterial;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class RawMaterialImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['material_name'])) {
            return;
        }

        RawMaterial::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'material_name' => $data['material_name'],
            ],
            [
                // kolom lain dibiarkan null
            ]
        );
    }

    public function rules(): array
    {
        return [
            'material_name' => 'required|string|max:255',
        ];
    }
}
