<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scale;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ScaleController extends Controller
{
    public function index()
    {
        $scales = Scale::with('area')->latest()->paginate(10);
        return view('scales.index', compact('scales'));
    }

    public function create()
    {
        return view('scales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $areaUuid = $user->area_uuid;

        Scale::create([
            'uuid' => Str::uuid(),
            'code' => $request->code,
            'type' => $request->type,
            'brand' => $request->brand,
            'area_uuid' => $areaUuid,
        ]);

        return redirect()->route('scales.index')->with('success', 'Timbangan berhasil ditambahkan.');
    }

    public function edit(string $uuid)
    {
        $scale = Scale::where('uuid', $uuid)->firstOrFail();
        return view('scales.edit', compact('scale'));
    }

    public function update(Request $request, string $uuid)
    {
        $scale = Scale::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
        ]);

        $scale->update([
            'code' => $request->code,
            'type' => $request->type,
            'brand' => $request->brand,
        ]);

        return redirect()->route('scales.index')->with('success', 'Timbangan berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $scale = Scale::where('uuid', $uuid)->firstOrFail();
        $scale->delete();

        return redirect()->route('scales.index')->with('success', 'Timbangan berhasil dihapus.');
    }
}