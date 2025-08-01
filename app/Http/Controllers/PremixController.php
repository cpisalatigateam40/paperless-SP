<?php

namespace App\Http\Controllers;

use App\Models\Premix;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremixController extends Controller
{
    public function index()
    {
        $premixes = Premix::with('area')->latest()->paginate(10);
        return view('premixes.index', compact('premixes'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('premixes.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'production_code' => 'required|string|max:255',
            'producer' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|integer',
        ]);

        $validated['area_uuid'] = Auth::user()->area_uuid;

        Premix::create($validated);

        return redirect()->route('premixes.index')->with('success', 'Premix created.');
    }

    public function edit($uuid)
    {
        $premix = Premix::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        return view('premixes.edit', compact('premix', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $premix = Premix::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'production_code' => 'nullable|string|max:255',
            'producer' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|integer',
        ]);

        $validated['area_uuid'] = Auth::user()->area_uuid;

        $premix->update($validated);

        return redirect()->route('premixes.index')->with('success', 'Premix updated.');
    }


    public function destroy($uuid)
    {
        $premix = Premix::where('uuid', $uuid)->firstOrFail();
        $premix->delete();
        return redirect()->route('premixes.index')->with('success', 'Premix deleted.');
    }
}