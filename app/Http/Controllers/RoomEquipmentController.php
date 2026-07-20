<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Area;
use App\Models\Room;
use App\Models\RoomElement;
use App\Models\Equipment;
use App\Models\EquipmentPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\RoomTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RoomImport;
use App\Exports\EquipmentTemplateExport;
use App\Imports\EquipmentImport;

class RoomEquipmentController extends Controller
{
public function index(Request $request)
{
    $search = $request->search;

    // ==========================
    // ROOM
    // ==========================
    $roomQuery = Room::with('elements');

    if (
        auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
        $request->filled('area')
    ) {
        $roomQuery->where('area_uuid', $request->area);
    }

    $rooms = $roomQuery
        ->when($search, function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhereHas('elements', function ($e) use ($search) {
                        $e->where('element_name', 'like', "%{$search}%");
                    });
            });
        })
        ->orderBy('name', 'asc')
        ->get();

    // ==========================
    // EQUIPMENT
    // ==========================
    $equipmentQuery = Equipment::with('parts');

    if (
        auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
        $request->filled('area')
    ) {
        $equipmentQuery->where('area_uuid', $request->area);
    }

    $equipments = $equipmentQuery
        ->when($search, function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhereHas('parts', function ($p) use ($search) {
                        $p->where('part_name', 'like', "%{$search}%");
                    });
            });
        })
        ->orderBy('name', 'asc')
        ->get();

    $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
        ? Area::orderBy('name')->get()
        : collect();

    return view('room_equipment.master_data', compact(
        'areas',
        'rooms',
        'equipments'
    ));
}

    // ---------- ROOM ----------
    public function storeRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'elements' => 'nullable|string',
        ]);

        $room = Room::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        $elements = array_map('trim', explode(',', $request->elements));

        foreach ($elements as $element) {
            if ($element !== '') {
                RoomElement::create([
                    'uuid' => (string) Str::uuid(),
                    'room_uuid' => $room->uuid,
                    'element_name' => $element
                ]);
            }
        }


        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }



    public function destroyRoom($uuid)
    {
        Room::where('uuid', $uuid)->delete();
        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    // ---------- EQUIPMENT ----------
    public function storeEquipment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parts' => 'nullable|string',
        ]);

        $equipment = Equipment::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        // Pecah string berdasarkan koma dan trim spasi
        $parts = array_map('trim', explode(',', $request->parts));

        foreach ($parts as $part) {
            if ($part !== '') {
                EquipmentPart::create([
                    'uuid' => (string) Str::uuid(),
                    'equipment_uuid' => $equipment->uuid,
                    'part_name' => $part
                ]);
            }
        }

        return back()->with('success', 'Mesin/peralatan berhasil ditambahkan.');
    }

    public function destroyEquipment($uuid)
    {
        DB::transaction(function () use ($uuid) {
            // Hapus detail yang berelasi dulu
            DB::table('detail_equipment_cleanliness')->where('equipment_uuid', $uuid)->delete();

            // Baru hapus equipment
            Equipment::where('uuid', $uuid)->delete();
        });

        return back()->with('success', 'Mesin/peralatan berhasil dihapus.');
    }

    public function downloadRoomTemplate()
    {
        return Excel::download(
            new RoomTemplateExport,
            'template-ruangan.xlsx'
        );
    }

    public function importRoom(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new RoomImport, $request->file('file'));

        return back()->with('success', 'Data ruangan berhasil diimport');
    }

    public function downloadEquipmentTemplate()
    {
        return Excel::download(
            new EquipmentTemplateExport,
            'template-mesin-peralatan.xlsx'
        );
    }

    public function importEquipment(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new EquipmentImport, $request->file('file'));

        return back()->with('success', 'Data mesin & peralatan berhasil diimport');
    }

}