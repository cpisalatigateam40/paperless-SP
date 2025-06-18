<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportConveyorCleanliness;
use App\Models\ConveyorMachine;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportConveyorCleanlinessController extends Controller
{
    public function index()
    {
        $reports = ReportConveyorCleanliness::with('area', 'section', 'machines')
            ->latest()
            ->get();

        return view('report_conveyor_cleanliness.index', compact('reports'));
    }

    public function create()
    {
        $sections = Section::orderBy('section_name')->get();
        return view('report_conveyor_cleanliness.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $uuid = Str::uuid();

        $report = ReportConveyorCleanliness::create([
            'uuid' => $uuid,
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
            'approved_at' => $request->approved_at,
        ]);

        $timeValue = $request->machines[0]['time'] ?? null;

        // Simpan detail mesin
        foreach ($request->machines ?? [] as $machine) {
            ConveyorMachine::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'time' => $machine['time'] ?? $timeValue,
                'machine_name' => $machine['machine_name'] ?? null,
                'status' => $machine['status'] ?? null,
                'qc_check' => isset($machine['qc_check']),
                'kr_check' => isset($machine['kr_check']),
                'notes' => $machine['notes'] ?? null,
                'corrective_action' => $machine['corrective_action'] ?? null,
                'verification' => $machine['verification'] ?? null,
            ]);
        }

        return redirect()->route('report-conveyor-cleanliness.index')->with('success', 'Laporan berhasil dibuat.');
    }

    public function destroy($uuid)
    {
        $report = ReportConveyorCleanliness::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetail($uuid)
    {
        $report = ReportConveyorCleanliness::where('uuid', $uuid)->firstOrFail();

        $mesins = [
            'Thermoformer Collimatic',
            'Thermoformer CFS',
            'Packing Manual 1',
            'Packing Manual 2',
        ];

        return view('report_conveyor_cleanliness.add_detail', compact('report', 'mesins'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $request->validate([
            'machines' => 'required|array|min:1',
            'machines.0.time' => 'required|date_format:H:i',
            'machines.*.machine_name' => 'required|string',
            'machines.*.status' => 'nullable|in:bersih,kotor',
            'machines.*.notes' => 'nullable|string',
            'machines.*.corrective_action' => 'nullable|string',
            'machines.*.verification' => 'nullable|string',
            'machines.*.qc_check' => 'nullable',
            'machines.*.kr_check' => 'nullable',
        ]);

        $report = ReportConveyorCleanliness::where('uuid', $uuid)->firstOrFail();

        $timeValue = $request->machines[0]['time'] ?? now()->format('H:i');

        foreach ($request->machines ?? [] as $machine) {
            ConveyorMachine::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'time' => $timeValue,
                'machine_name' => $machine['machine_name'],
                'status' => $machine['status'] ?? null,
                'qc_check' => isset($machine['qc_check']),
                'kr_check' => isset($machine['kr_check']),
                'notes' => $machine['notes'] ?? null,
                'corrective_action' => $machine['corrective_action'] ?? null,
                'verification' => $machine['verification'] ?? null,
            ]);
        }

        return redirect()->route('report-conveyor-cleanliness.index')
            ->with('success', 'Detail inspeksi berhasil ditambahkan.');
    }

}