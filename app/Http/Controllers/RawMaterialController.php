<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Area;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = RawMaterial::with('area')->get();
        return view('raw_material.index', compact('rawMaterials'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('raw_material.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_name' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'area_uuid' => 'nullable|exists:areas,uuid',
        ]);

        RawMaterial::create($request->all());
        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $rawMaterial = RawMaterial::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        return view('raw_material.edit', compact('rawMaterial', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'material_name' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'area_uuid' => 'nullable|exists:areas,uuid',
        ]);

        $rawMaterial = RawMaterial::where('uuid', $uuid)->firstOrFail();
        $rawMaterial->update($request->all());

        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $rawMaterial = RawMaterial::where('uuid', $uuid)->firstOrFail();
        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil dihapus.');
    }
}