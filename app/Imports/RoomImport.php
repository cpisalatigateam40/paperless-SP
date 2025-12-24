<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\RoomElement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\DB;

class RoomImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (empty($data['name'])) {
            return;
        }

        DB::transaction(function () use ($data) {

            // 1. Create / Update Room
            $room = Room::updateOrCreate(
                [
                    'area_uuid' => Auth::user()->area_uuid,
                    'name' => $data['name'],
                ]
            );

            // 2. Sync Room Elements
            if (!empty($data['elements'])) {

                $elements = array_unique(array_filter(
                    array_map('trim', explode(',', $data['elements']))
                ));

                // hapus element lama
                RoomElement::where('room_uuid', $room->uuid)->delete();

                foreach ($elements as $element) {
                    RoomElement::create([
                        'uuid' => (string) Str::uuid(),
                        'room_uuid' => $room->uuid,
                        'element_name' => $element,
                    ]);
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'elements' => 'nullable|string',
        ];
    }
}
