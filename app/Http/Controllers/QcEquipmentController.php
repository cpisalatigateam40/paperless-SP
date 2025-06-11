<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QcEquipment;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class QcEquipmentController extends Controller
{
    public function index()
    {
        $qcEquipments = QcEquipment::with('area')->latest()->paginate(10);
        return view('qc_equipment.index', compact('qcEquipments'));
    }

    public function create()
    {
        return view('qc_equipment.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'section_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();
        $areaUuid = $user->area_uuid;

        QcEquipment::create([
            'uuid' => Str::uuid(),
            'item_name' => $request->item_name,
            'section_name' => $request->section_name,
            'quantity' => $request->quantity,
            'area_uuid' => $areaUuid,
        ]);

        return redirect()->route('qc-equipment.index')->with('success', 'Item created successfully.');
    }

    public function edit(string $uuid)
    {
        $qcEquipment = QcEquipment::where('uuid', $uuid)->firstOrFail();
        return view('qc_equipment.edit', compact('qcEquipment'));
    }

    public function update(Request $request, string $uuid)
    {
        $qcEquipment = QcEquipment::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'item_name' => 'required|string|max:255',
            'section_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $qcEquipment->update($request->only(['item_name', 'section_name', 'quantity']));

        return redirect()->route('qc-equipment.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $qcEquipment = QcEquipment::where('uuid', $uuid)->firstOrFail();
        $qcEquipment->delete();

        return redirect()->route('qc-equipment.index')->with('success', 'Item deleted successfully.');
    }
}