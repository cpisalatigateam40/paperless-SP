<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class RawMaterialController extends Controller
{
    public function index()
    {
        $rawMaterials = RawMaterial::with('area')->orderBy('material_name', 'asc')->paginate(10);
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
            'shelf_life' => 'nullable|integer|min:0',
        ]);

        RawMaterial::create([
            'uuid' => Str::uuid(),
            'material_name' => $request->material_name,
            'supplier' => $request->supplier,
            'shelf_life' => $request->shelf_life,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

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
            'shelf_life' => 'nullable|integer|min:0',
        ]);

        $rawMaterial = RawMaterial::where('uuid', $uuid)->firstOrFail();

        $rawMaterial->update([
            'material_name' => $request->material_name,
            'supplier' => $request->supplier,
            'shelf_life' => $request->shelf_life,
            'area_uuid' => $request->area_uuid ?? Auth::user()->area_uuid,
        ]);

        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil diperbarui.');
    }


    public function destroy($uuid)
    {
        $rawMaterial = RawMaterial::where('uuid', $uuid)->firstOrFail();
        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        // Baca file dan loop datanya
        $data = Excel::toArray([], $file);
        $rows = $data[0]; // sheet pertama

        foreach ($rows as $index => $row) {
            // Lewati baris header, sesuaikan jika header ada di baris pertama
            if ($index === 0)
                continue;

            // Pastikan kolom sesuai urutan: material_name, supplier, shelf_life
            $materialName = $row[0] ?? null;
            $supplier = $row[1] ?? null;
            $shelfLife = $row[2] ?? null;

            // Validasi data per baris
            if (!$materialName)
                continue; // Lewati jika nama kosong

            RawMaterial::create([
                'uuid' => Str::uuid(),
                'material_name' => $materialName,
                'supplier' => $supplier,
                'shelf_life' => $shelfLife ? str_replace(',', '.', strval($shelfLife)) : null,
                'area_uuid' => Auth::user()->area_uuid,
            ]);
        }

        return redirect()->route('raw-materials.index')->with('success', 'Data berhasil diimport.');
    }
}