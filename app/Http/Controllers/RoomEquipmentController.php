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
        $search  = $request->search;
        $isAdmin = auth()->user()->hasAnyRole(['admin', 'superadmin']);

        // ==========================
        // ROOM
        // ==========================
        $roomQuery = Room::with('elements');

        if ($isAdmin && $request->filled('area')) {
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
        $equipmentQuery = Equipment::with('parts', 'room');

        if ($isAdmin && $request->filled('area')) {
            $equipmentQuery->where('area_uuid', $request->area);
        }

        $equipments = $equipmentQuery
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('parts', fn ($p) => $p->where('part_name', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn ($r) => $r->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        // ==========================
        // DROPDOWN
        // ==========================
        $areas = $isAdmin
            ? Area::orderBy('name')->get()
            : collect();

        // Daftar ruangan untuk dropdown form mesin (tidak terpengaruh search)
        $roomOptions = Room::query()
            ->when($isAdmin && $request->filled('area'), fn ($q) => $q->where('area_uuid', $request->area))
            ->orderBy('name')
            ->get();

        return view('room_equipment.master_data', compact(
            'areas',
            'rooms',
            'equipments',
            'roomOptions'
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
        DB::transaction(function () use ($uuid) {
            Equipment::where('room_uuid', $uuid)->update(['room_uuid' => null]);
            Room::where('uuid', $uuid)->delete();
        });

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    // ---------- EQUIPMENT ----------
    public function storeEquipment(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'room_uuid' => 'required|string',
            'parts'     => 'nullable|string',
        ]);

        // Room ber-UserAreaScope, jadi uuid dari area lain otomatis 404
        $room = Room::where('uuid', $request->room_uuid)->firstOrFail();

        $equipment = Equipment::create([
            'name'      => $request->name,
            'area_uuid' => $room->area_uuid,
            'room_uuid' => $room->uuid,
        ]);

        foreach (array_map('trim', explode(',', (string) $request->parts)) as $part) {
            if ($part !== '') {
                EquipmentPart::create([
                    'equipment_uuid' => $equipment->uuid,
                    'part_name'      => $part,
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

    public function updateEquipment(Request $request, $uuid)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'room_uuid' => 'required|string',
            'parts'     => 'nullable|string',
        ]);

        $equipment = Equipment::where('uuid', $uuid)->firstOrFail();
        $room      = Room::where('uuid', $request->room_uuid)->firstOrFail();

        DB::transaction(function () use ($request, $equipment, $room) {
            $equipment->update([
                'name'      => $request->name,
                'room_uuid' => $room->uuid,
                'area_uuid' => $room->area_uuid,
            ]);

            $newParts = collect(explode(',', (string) $request->parts))
                ->map(fn ($p) => trim($p))
                ->filter()
                ->unique()
                ->values();

            $existing = EquipmentPart::where('equipment_uuid', $equipment->uuid)->get();

            // Hapus part yang dibuang dari daftar
            $existing->reject(fn ($p) => $newParts->contains($p->part_name))
                ->each(fn ($p) => $p->delete());

            // Tambah part baru
            $keep = $existing->pluck('part_name');
            $newParts->reject(fn ($n) => $keep->contains($n))->each(function ($name) use ($equipment) {
                EquipmentPart::create([
                    'equipment_uuid' => $equipment->uuid,
                    'part_name'      => $name,
                ]);
            });
        });

        return back()->with('success', 'Mesin/peralatan berhasil diperbarui.');
    }

}