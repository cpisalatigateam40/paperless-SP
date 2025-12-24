<?php

namespace App\Imports;

use App\Models\Scale;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;

class ScaleImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['code'])) {
            return;
        }

        Scale::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'code' => $data['code'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'type' => $data['type'],
                'brand' => $data['brand'],
                'owner' => $data['owner'],
            ]
        );
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'owner' => 'required|string|max:255',
        ];
    }
}
