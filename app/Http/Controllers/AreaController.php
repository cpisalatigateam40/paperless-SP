<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::all();
        return view('areas.area', compact('areas'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:areas,name|max:255',
        ]);

        Area::create(['name' => $request->name]);

        return redirect()->route('areas.index')->with('success', 'Area berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $area = Area::where('uuid', $uuid)->firstOrFail();
        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, $uuid)
    {
        $area = Area::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'name' => 'required|unique:areas,name,' . $area->id,
        ]);

        $area->update(['name' => $request->name]);

        return redirect()->route('areas.index')->with('success', 'Area berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $area = Area::where('uuid', $uuid)->firstOrFail();
        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Area berhasil dihapus.');
    }
}