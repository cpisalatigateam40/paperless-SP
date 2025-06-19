<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\SolventItem;
use App\Models\ReportSolvent;
use App\Models\DetailSolvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportSolventController extends Controller
{
    public function index()
    {
        $reports = ReportSolvent::with('details', 'area')->latest()->get();
        return view('report_solvents.index', compact('reports'));
    }

    public function create()
    {
        // Auto seed jika solvent_items kosong
        if (SolventItem::count() === 0) {
            $items = [
                ['name' => 'METTA KLIN', 'concentration' => '2%', 'volume_material' => 1500, 'volume_solvent' => 50000, 'application_area' => 'MESIN DAN PERALATAN'],
                ['name' => 'DIVERFOAM', 'concentration' => '5%', 'volume_material' => 1250, 'volume_solvent' => 25000, 'application_area' => 'MESIN DAN PERALATAN'],
                ['name' => 'METTA QUART', 'concentration' => '800 PPM', 'volume_material' => 160, 'volume_solvent' => 20000, 'application_area' => 'SANITIZER MESIN DAN PERALATAN'],
                ['name' => 'KLORIN 12%', 'concentration' => '50 PPM', 'volume_material' => 20, 'volume_solvent' => 48000, 'application_area' => 'HAND BASIN KORIDOR MP'],
                ['name' => 'KLORIN 12%', 'concentration' => '200 PPM', 'volume_material' => 742, 'volume_solvent' => 445000, 'application_area' => 'FOOT BASIN KORIDOR MP'],
                ['name' => 'KLORIN 12%', 'concentration' => '50 PPM', 'volume_material' => 20, 'volume_solvent' => 48000, 'application_area' => 'HAND BASIN KORIDOR MP'],
                ['name' => 'KLORIN 12%', 'concentration' => '200 PPM', 'volume_material' => 742, 'volume_solvent' => 445000, 'application_area' => 'FOOT BASIN KORIDOR MP'],
            ];

            foreach ($items as $item) {
                SolventItem::create([
                    'uuid' => Str::uuid(),
                    'name' => $item['name'],
                    'concentration' => $item['concentration'],
                    'volume_material' => $item['volume_material'],
                    'volume_solvent' => $item['volume_solvent'],
                    'application_area' => $item['application_area'],
                ]);
            }
        }

        $areas = Area::all();
        $solventItems = SolventItem::all();
        return view('report_solvents.create', compact('areas', 'solventItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
        ]);

        $uuid = Str::uuid();

        $report = ReportSolvent::create([
            'uuid' => $uuid,
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details ?? [] as $detail) {
            DetailSolvent::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'solvent_uuid' => $detail['solvent_uuid'],
                'verification_result' => isset($detail['verification_result']),
                'corrective_action' => $detail['corrective_action'] ?? null,
                'reverification_action' => $detail['reverification_action'] ?? null,
            ]);
        }

        return redirect()->route('report-solvents.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportSolvent::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportSolvent::findOrFail($id);
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
        $report = ReportSolvent::with(['area', 'details.solvent'])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_solvents.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_larutan_' . $report->date . '.pdf');
    }
}