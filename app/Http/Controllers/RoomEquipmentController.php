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

class RoomEquipmentController extends Controller
{
    public function index()
    {
        return view('room_equipment.master_data', [
            'areas' => Area::all(),
            'rooms' => Room::with('elements')->get(),
            'equipments' => Equipment::with('parts')->get(),
        ]);
    }

    // ---------- ROOM ----------
    public function storeRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'elements' => 'array',
        ]);

        $room = Room::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        foreach ($request->elements ?? [] as $element) {
            RoomElement::create([
                'uuid' => (string) Str::uuid(),
                'room_uuid' => $room->uuid,
                'element_name' => $element
            ]);
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
            'parts' => 'array',
        ]);

        $equipment = Equipment::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        foreach ($request->parts ?? [] as $part) {
            EquipmentPart::create([
                'uuid' => (string) Str::uuid(),
                'equipment_uuid' => $equipment->uuid,
                'part_name' => $part
            ]);
        }

        return back()->with('success', 'Mesin/peralatan berhasil ditambahkan.');
    }

    public function destroyEquipment($uuid)
    {
        Equipment::where('uuid', $uuid)->delete();
        return back()->with('success', 'Mesin/peralatan berhasil dihapus.');
    }
}