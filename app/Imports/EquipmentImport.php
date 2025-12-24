<?php

namespace App\Imports;

use App\Models\Equipment;
use App\Models\EquipmentPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class EquipmentImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['name'])) {
            return;
        }

        DB::transaction(function () use ($data) {

            // 1. Create / Update Equipment
            $equipment = Equipment::updateOrCreate(
                [
                    'area_uuid' => Auth::user()->area_uuid,
                    'name' => $data['name'],
                ]
            );

            // 2. Sync Parts
            if (!empty($data['parts'])) {

                $parts = array_unique(array_filter(
                    array_map('trim', explode(',', $data['parts']))
                ));

                // hapus part lama
                EquipmentPart::where('equipment_uuid', $equipment->uuid)->delete();

                foreach ($parts as $part) {
                    EquipmentPart::create([
                        'uuid' => (string) Str::uuid(),
                        'equipment_uuid' => $equipment->uuid,
                        'part_name' => $part,
                    ]);
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parts' => 'nullable|string',
        ];
    }
}
