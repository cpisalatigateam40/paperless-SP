<?php

namespace App\Imports;

use App\Models\Thermometer;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class ThermometerImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['code'])) {
            return;
        }

        Thermometer::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'code' => $data['code'],
            ],
            [
                'type' => $data['type'],
                'brand' => $data['brand'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
        ];
    }
}
