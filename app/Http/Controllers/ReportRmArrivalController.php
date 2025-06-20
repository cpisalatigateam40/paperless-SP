<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportRmArrival;
use App\Models\DetailRmArrival;
use App\Models\Area;
use App\Models\RawMaterial;

class ReportRmArrivalController extends Controller
{
    public function index()
    {
        $reports = ReportRmArrival::with('area', 'details.rawMaterial')
            ->orderByDesc('date')
            ->get();

        return view('report_rm_arrivals.index', compact('reports'));
    }

    public function create()
    {
        return view('report_rm_arrivals.create', [
            'areas' => Area::all(),
            'rawMaterials' => RawMaterial::all(),
        ]);
    }

    public function store(Request $request)
    {
        $report = ReportRmArrival::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->input('details', []) as $detail) {
            DetailRmArrival::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'raw_material_uuid' => $detail['raw_material_uuid'],
                'production_code' => $detail['production_code'] ?? null,
                'time' => $detail['time'],
                'temperature' => $detail['temperature'],
                'packaging_condition' => $detail['packaging_condition'],
                'sensorial_condition' => $detail['sensorial_condition'],
                'problem' => $detail['problem'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
            ]);
        }

        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Laporan kedatangan bahan baku berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportRmArrival::where('uuid', $uuid)->firstOrFail();

        DetailRmArrival::where('report_uuid', $report->uuid)->delete();

        $report->delete();

        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }
}
