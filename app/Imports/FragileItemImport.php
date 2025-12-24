<?php

namespace App\Imports;

use App\Models\FragileItem;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Str;

class FragileItemImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        FragileItem::updateOrCreate(
            [
                'area_uuid' => Auth::user()->area_uuid,
                'item_name' => $data['item_name'],
            ],
            [
                'uuid' => (string) Str::uuid(),
                'section_name' => $data['section_name'] ?? null,
                'owner' => $data['owner'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'item_name' => 'required|string|max:255',
            'section_name' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
        ];
    }
}
