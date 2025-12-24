<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thermometer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Exports\ThermometerTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ThermometerImport;

class ThermometerController extends Controller
{
    public function index(Request $request)
    {
        $query = Thermometer::with('area')
            ->orderBy('brand', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $thermometers = $query->paginate(10)->withQueryString();

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

    public function downloadTemplate()
    {
        return Excel::download(
            new ThermometerTemplateExport,
            'template-thermometer.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new ThermometerImport, $request->file('file'));

        return redirect()
            ->route('thermometers.index')
            ->with('success', 'Data thermometer berhasil diimport');
    }
}