<?php

namespace App\Http\Controllers;

use App\Models\MetalDetector;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MetalDetectorController extends Controller
{
    public function index(Request $request)
    {
        $query = MetalDetector::latest();

        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        $metalDetectors = $query->paginate(10)->withQueryString();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('metal_detectors.index', compact('metalDetectors', 'areas'));
    }

    public function create()
    {
        return view('metal_detectors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'merk' => 'required|string|max:255',
            'type_model' => 'required|string|max:255',
            'no_series' => 'required|string|max:255',
        ]);

        MetalDetector::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'merk' => $request->merk,
            'type_model' => $request->type_model,
            'no_series' => $request->no_series,
            'is_active' => true,
        ]);

        return redirect()->route('metal_detectors.index')
            ->with('success', 'Master data Metal Detector berhasil ditambahkan.');
    }

    public function edit(MetalDetector $metalDetector)
    {
        return view('metal_detectors.edit', compact('metalDetector'));
    }

    public function update(Request $request, MetalDetector $metalDetector)
    {
        $request->validate([
            'merk' => 'required|string|max:255',
            'type_model' => 'required|string|max:255',
            'no_series' => 'required|string|max:255',
        ]);

        $metalDetector->update([
            'merk' => $request->merk,
            'type_model' => $request->type_model,
            'no_series' => $request->no_series,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('metal_detectors.index')
            ->with('success', 'Master data Metal Detector berhasil diperbarui.');
    }

    public function destroy(MetalDetector $metalDetector)
    {
        $metalDetector->delete();

        return redirect()->route('metal_detectors.index')
            ->with('success', 'Master data Metal Detector berhasil dihapus.');
    }
}