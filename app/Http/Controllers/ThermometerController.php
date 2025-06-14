<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thermometer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThermometerController extends Controller
{
    public function index()
    {
        $thermometers = Thermometer::with('area')->latest()->paginate(10);
        return view('thermometers.index', compact('thermometers'));
    }

    public function create()
    {
        return view('thermometers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
        ]);

        Thermometer::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'code' => $request->code,
            'type' => $request->type,
            'brand' => $request->brand,
        ]);

        return redirect()->route('thermometers.index')->with('success', 'Data thermometer berhasil ditambahkan.');
    }

    public function edit(string $uuid)
    {
        $thermometer = Thermometer::where('uuid', $uuid)->firstOrFail();
        return view('thermometers.edit', compact('thermometer'));
    }

    public function update(Request $request, string $uuid)
    {
        $thermometer = Thermometer::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'code' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
        ]);

        $thermometer->update($request->only(['code', 'type', 'brand']));

        return redirect()->route('thermometers.index')->with('success', 'Data thermometer berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $thermometer = Thermometer::where('uuid', $uuid)->firstOrFail();
        $thermometer->delete();

        return redirect()->route('thermometers.index')->with('success', 'Data thermometer berhasil dihapus.');
    }
}