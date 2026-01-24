<?php

namespace App\Imports;

use App\Models\Premix;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows; // ✅
use Maatwebsite\Excel\Row;

class PremixImport implements
    OnEachRow,
    WithHeadingRow,
    WithValidation,
    SkipsEmptyRows
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // safety check
        if (empty(trim($data['name'] ?? ''))) {
            return;
        }

        Premix::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'name' => trim($data['name']),
            ],
            [
                'producer'   => $data['producer'] ?? null,
                'shelf_life' => isset($data['shelf_life'])
                    ? (string) $data['shelf_life']
                    : null,
                'unit'       => $data['unit'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'producer'   => 'nullable|string|max:255',
            'shelf_life' => 'nullable|max:255',
            'unit'       => 'nullable|in:Jam,Hari,Bulan,Tahun',
        ];
    }
}
