<?php

namespace App\Http\Controllers;

use App\Models\SharpTool;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SharpToolController extends Controller
{
    public function index()
    {
        $sharpTools = SharpTool::with('area')->latest()->get();
        return view('sharp_tools.index', compact('sharpTools'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('sharp_tools.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        SharpTool::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
            'quantity' => $request->quantity,
        ]);

        return redirect()->route('sharp_tools.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit($uuid)
    {
        $sharpTool = SharpTool::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        return view('sharp_tools.edit', compact('sharpTool', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $sharpTool = SharpTool::where('uuid', $uuid)->firstOrFail();
        $sharpTool->update([
            'name' => $request->name,
            'area_uuid' => Auth::user()->area_uuid,
            'quantity' => $request->quantity,
        ]);

        return redirect()->route('sharp_tools.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $sharpTool = SharpTool::where('uuid', $uuid)->firstOrFail();
        $sharpTool->delete();
        return redirect()->route('sharp_tools.index')->with('success', 'Data berhasil dihapus.');
    }
}