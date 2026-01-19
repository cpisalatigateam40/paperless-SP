<?php

namespace App\Http\Controllers;

use App\Models\Premix;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\PremixTemplateExport;
use App\Imports\PremixImport;

class PremixController extends Controller
{
    public function index(Request $request)
    {
        $query = Premix::with('area')
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('producer', 'like', "%{$search}%")
                ->orWhere('production_code', 'like', "%{$search}%");
            });
        }

        $premixes = $query->paginate(10)->withQueryString();

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
            // 'production_code' => 'required|string|max:255',
            'producer' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
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
            // 'production_code' => 'nullable|string|max:255',
            'producer' => 'nullable|string|max:255',
            'shelf_life' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
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

    
    public function downloadTemplate()
    {
        return Excel::download(
            new PremixTemplateExport,
            'template-premix.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new PremixImport, $request->file('file'));

        return redirect()
            ->route('premixes.index')
            ->with('success', 'Data premix berhasil diimport');
    }
}