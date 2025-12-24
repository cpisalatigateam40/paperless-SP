<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scale;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Exports\ScaleTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ScaleImport;

class ScaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Scale::with('area')
            ->orderBy('brand', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('owner', 'like', "%{$search}%");
            });
        }

        $scales = $query->paginate(10)->withQueryString();

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
            'owner' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $areaUuid = $user->area_uuid;

        Scale::create([
            'uuid' => Str::uuid(),
            'code' => $request->code,
            'type' => $request->type,
            'brand' => $request->brand,
            'owner' => $request->owner,
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
            'owner' => 'required|string|max:255',
        ]);

        $scale->update([
            'code' => $request->code,
            'type' => $request->type,
            'brand' => $request->brand,
            'owner' => $request->owner,
        ]);

        return redirect()->route('scales.index')->with('success', 'Timbangan berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $scale = Scale::where('uuid', $uuid)->firstOrFail();
        $scale->delete();

        return redirect()->route('scales.index')->with('success', 'Timbangan berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new ScaleTemplateExport,
            'template-timbangan.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new ScaleImport, $request->file('file'));

        return redirect()
            ->route('scales.index')
            ->with('success', 'Data timbangan berhasil diimport');
    }
}