<?php

namespace App\Imports;

use App\Models\Premix;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class PremixImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['name'])) {
            return;
        }

        Premix::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'name' => $data['name'],
            ],
            [
                'producer' => $data['producer'] ?? null,
                'shelf_life' => $data['shelf_life'] ?? null,
                // production_code dibiarkan null
            ]
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'producer' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|string|max:255',
        ];
    }
}
