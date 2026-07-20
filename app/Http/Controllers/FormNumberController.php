<?php

namespace App\Http\Controllers;

use App\Models\FormNumber;
use App\Models\Area;
use Illuminate\Http\Request;

class FormNumberController extends Controller
{
    public function index(Request $request)
    {
        $query = FormNumber::with('area');

        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        $formNumbers = $query
            ->latest()
            ->get();

        $reportTypes = config('report_types');

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('form-numbers.index', compact(
            'formNumbers',
            'reportTypes',
            'areas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'form_number' => 'required|string|max:50',
        ]);

        FormNumber::updateOrCreate(
            [
                'area_uuid' => auth()->user()->area_uuid,
                'report_type' => $request->report_type,
            ],
            ['form_number' => $request->form_number]
        );

        return back()->with('success', 'Nomor form disimpan.');
    }

    public function edit(FormNumber $formNumber)
    {
        return response()->json($formNumber);
    }

    public function update(Request $request, FormNumber $formNumber)
    {
        $request->validate([
            'form_number' => 'required|string|max:50',
        ]);

        $formNumber->update(['form_number' => $request->form_number]);

        return back()->with('success', 'Nomor form diperbarui.');
    }

    public function destroy(FormNumber $formNumber)
    {
        $formNumber->delete();

        return back()->with('success', 'Nomor form dihapus.');
    }
}