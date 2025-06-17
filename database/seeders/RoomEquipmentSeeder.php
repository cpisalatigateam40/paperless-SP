<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Room;
use App\Models\RoomElement;
use App\Models\Equipment;
use App\Models\EquipmentPart;
use App\Models\Area;

class RoomEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $area = Area::firstOrCreate(
            ['name' => 'Salatiga'],
            ['uuid' => (string) Str::uuid()]
        );

        // -----------------------
        // ✅ Ruangan dan Elemennya
        // -----------------------
        $rooms = [
            'BUFFER SEASONING' => ['Dinding', 'Lantai', 'Langit-langit', 'Lampu + cover', 'Curtain', 'Exhaust Fan', 'Pintu', 'Rak penyimpanan'],
            'CHILLROOM' => ['Dinding', 'Lantai', 'Langit-langit', 'Lampu + cover', 'Pintu', 'Curtain', 'Rak penyimpanan', 'Saluran air buangan'],
            'MEAT PREPARATION' => ['Dinding', 'Lantai', 'Langit-langit', 'Lampu + cover', 'Curtain', 'Evaporator', 'Saluran air buangan'],
            'COOKING' => ['Dinding', 'Lantai', 'Langit-langit', 'Lampu + cover', 'Pintu', 'Curtain', 'Saluran air buangan', 'Pipa asap', 'Exhaust fan'],
            'ABF' => ['Dinding', 'Lantai', 'Langit-langit', 'Exhaust Fan', 'Lampu + cover', 'Pintu'],
            'IQF' => ['Dinding', 'Lantai', 'Langit-langit', 'Exhaust Fan', 'Lampu + cover', 'Conveyor'],
            'PACKING' => ['Dinding', 'Lantai', 'Langit-langit', 'Evaporator', 'Lampu + cover', 'Curtain', 'Saluran air buangan'],
            'DRY STORE' => ['Lantai', 'Langit-langit', 'Curtain', 'Pintu', 'Jendela', 'Rak Penyimpanan', 'Tangga', 'Lampu + cover'],
        ];

        foreach ($rooms as $roomName => $elements) {
            $room = Room::create([
                'uuid' => (string) Str::uuid(),
                'name' => $roomName,
                'area_uuid' => $area->uuid,
            ]);

            foreach ($elements as $element) {
                RoomElement::create([
                    'uuid' => (string) Str::uuid(),
                    'room_uuid' => $room->uuid,
                    'element_name' => $element,
                ]);
            }
        }

        // ----------------------------------
        // ✅ Mesin/Peralatan dan Part-nya
        // ----------------------------------
        $equipments = [
            'Autogrind' => ['Saringan', 'Screw', 'Mata pisau', 'Loader', 'Dinding Dalam Hooper', 'Dinding luar hooper'],
            'Unimix' => ['Dinding Dalam Mesin', 'Dinding Luar Mesin', 'Loader', 'Screw', 'Feeding pump', 'Screen', 'Panel'],
            'Bowl Cutter' => ['Screw', 'Bowl', 'Loader', 'Pisau', 'Dinding luar', 'Panel', 'Cover Atas'],
            'Metal Detector Loma (MP)' => [],
            'Emulsifying' => ['Cover atas', 'Dinding dalam mesin', 'Dinding luar mesin', 'Screw', 'Pipa emulsi'],
            'Magnet Trap' => [],
            'Stuffer Hitech' => ['Dinding dalam mesin', 'Dinding luar mesin'],
            'Smoke House' => ['Dinding dalam mesin', 'Dinding luar mesin', 'Thermocouple', 'Panel', 'Pintu', 'Langit-langit', 'Tungku pengasapan'],
            'Cooling Chamber' => ['Dinding dalam', 'Dinding luar', 'Kran Showering'],
            'Former Baso' => ['Dinding dalam hooper', 'Dinding luar hooper'],
            'Boiling Tank' => ['Dinding dalam', 'Dinding luar', 'Conveyor', 'Panel'],
            'Mesin Steamer' => ['Dinding dalam', 'Dinding luar', 'Panel', 'Conveyor'],
            'Sausage cutter' => ['Pisau', 'Belt cutter', 'Cover penutup', 'Dinding luar mesin', 'Meja cutter'],
            'Check Weigher Box' => ['Conveyor', 'Panel'],
            'Mesin Gyoza' => ['Mesin Mixing Adonan', 'Mixer', 'Dinding Luar', 'Dinding Dalam', 'Mesin Pencetak Kulit Gyoza', 'Hopper adonan kulit', 'Belt conveyor', 'Mesin Filling Gyoza', 'Hopper adonan kulit gyoza', 'Body mesin luar', 'Belt conveyor'],
        ];

        foreach ($equipments as $equipmentName => $parts) {
            $equipment = Equipment::create([
                'uuid' => (string) Str::uuid(),
                'name' => $equipmentName,
                'area_uuid' => $area->uuid,
            ]);

            foreach ($parts as $part) {
                EquipmentPart::create([
                    'uuid' => (string) Str::uuid(),
                    'equipment_uuid' => $equipment->uuid,
                    'part_name' => $part,
                ]);
            }
        }
    }
}