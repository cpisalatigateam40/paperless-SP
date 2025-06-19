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

        foreach ($request->machines ?? [] as $machine) {
            ConveyorMachine::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'time' => $machine['time'] ?? now()->format('H:i'),
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
            'machines.*.machine_name' => 'required|string',
            'machines.*.status' => 'nullable|in:bersih,kotor',
            'machines.*.notes' => 'nullable|string',
            'machines.*.corrective_action' => 'nullable|string',
            'machines.*.verification' => 'nullable|string',
            'machines.*.qc_check' => 'nullable',
            'machines.*.kr_check' => 'nullable',
        ]);

        $report = ReportConveyorCleanliness::where('uuid', $uuid)->firstOrFail();

        foreach ($request->machines ?? [] as $machine) {
            ConveyorMachine::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'time' => $machine['time'] ?? now()->format('H:i'),
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

    public function edit($uuid)
    {
        $report = ReportConveyorCleanliness::with('machines')->where('uuid', $uuid)->firstOrFail();
        $sections = Section::orderBy('section_name')->get();

        return view('report_conveyor_cleanliness.edit', compact('report', 'sections'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportConveyorCleanliness::where('uuid', $uuid)->firstOrFail();

        // Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'section_uuid' => $request->section_uuid,
        ]);

        // Update machines
        foreach ($request->machines ?? [] as $group) {
            foreach ($group as $machineData) {
                if (isset($machineData['uuid'])) {
                    // Update existing
                    $machine = ConveyorMachine::where('uuid', $machineData['uuid'])->first();
                    if ($machine) {
                        $machine->update([
                            'time' => $machineData['time'] ?? $machine->time,
                            'status' => $machineData['status'] ?? null,
                            'notes' => $machineData['notes'] ?? null,
                            'corrective_action' => $machineData['corrective_action'] ?? null,
                            'verification' => $machineData['verification'] ?? null,
                            'qc_check' => isset($machineData['qc_check']),
                            'kr_check' => isset($machineData['kr_check']),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('report-conveyor-cleanliness.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function approve($id)
    {
        $report = ReportConveyorCleanliness::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportConveyorCleanliness::with(['area', 'section', 'machines'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $grouped = $report->machines->chunk(4);

        // Generate QR untuk created_by
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

        // Generate QR untuk approved_by
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $pdf = Pdf::loadView('report_conveyor_cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan_conveyor_' . $report->date . '.pdf');
    }
}