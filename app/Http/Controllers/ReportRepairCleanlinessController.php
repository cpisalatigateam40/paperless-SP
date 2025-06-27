<?php

namespace App\Http\Controllers;

use App\Models\ReportRepairCleanliness;
use App\Models\DetailRepairCleanliness;
use App\Models\Equipment;
use App\Models\Section;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRepairCleanlinessController extends Controller
{
    public function index()
    {
        $reports = ReportRepairCleanliness::with([
            'area',
            'details.equipment',
            'details.section'
        ])->latest()->paginate(10);

        return view('repair_cleanliness.index', compact('reports'));
    }

    public function create()
    {
        $equipments = Equipment::all();
        $sections = Section::all();
        $areas = Area::all();
        return view('repair_cleanliness.create', compact('equipments', 'sections', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required',
            'details' => 'required|array',
        ]);

        $report = ReportRepairCleanliness::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $detail) {
            DetailRepairCleanliness::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'equipment_uuid' => $detail['equipment_uuid'],
                'section_uuid' => $detail['section_uuid'],
                'repair_type' => $detail['repair_type'],
                'clean_condition' => $detail['clean_condition'],
                'spare_part_left' => $detail['spare_part_left'],
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        return redirect()->route('repair-cleanliness.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportRepairCleanliness::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('repair-cleanliness.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportRepairCleanliness::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function createDetail($uuid)
    {
        $report = ReportRepairCleanliness::where('uuid', $uuid)->firstOrFail();
        $equipments = Equipment::all();
        $sections = Section::all();

        return view('repair_cleanliness.add_detail', compact('report', 'equipments', 'sections'));
    }

    public function storeDetail(Request $request)
    {
        $validated = $request->validate([
            'report_uuid' => 'required|exists:report_repair_cleanliness,uuid',
            'equipment_uuid' => 'required|exists:equipments,uuid',
            'section_uuid' => 'required|exists:sections,uuid',
            'repair_type' => 'required|string|max:255',
            'post_repair_condition' => 'required|string|max:255',
            'clean_condition' => 'required|in:bersih,kotor',
            'spare_part_left' => 'required|in:ada,tidak ada',
            'notes' => 'nullable|string|max:255',
        ]);

        DetailRepairCleanliness::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $validated['report_uuid'],
            'equipment_uuid' => $validated['equipment_uuid'],
            'section_uuid' => $validated['section_uuid'],
            'repair_type' => $validated['repair_type'],
            'post_repair_condition' => $validated['post_repair_condition'],
            'clean_condition' => $validated['clean_condition'],
            'spare_part_left' => $validated['spare_part_left'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('repair-cleanliness.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportRepairCleanliness::with([
            'details.equipment',
            'details.section',
            'area',
        ])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('repair_cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Pemeriksaan_Repair_Cleanliness_' . $report->date . '.pdf');
    }

}