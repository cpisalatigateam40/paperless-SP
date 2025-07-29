<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportRmArrival;
use App\Models\DetailRmArrival;
use App\Models\Area;
use App\Models\RawMaterial;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function addDetail($uuid)
    {
        $report = ReportRmArrival::with('details')->where('uuid', $uuid)->firstOrFail();
        $rawMaterials = RawMaterial::all();

        return view('report_rm_arrivals.add_detail', compact('report', 'rawMaterials'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportRmArrival::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'details.*.raw_material_uuid' => 'required|exists:raw_materials,uuid',
            'details.*.production_code' => 'required|string',
            'details.*.time' => 'nullable',
            'details.*.temperature' => 'nullable|numeric',
            'details.*.packaging_condition' => 'nullable|string',
            'details.*.sensorial_condition' => 'nullable|string',
            'details.*.problem' => 'nullable|string',
            'details.*.corrective_action' => 'nullable|string',
        ]);

        foreach ($request->details as $detail) {
            DetailRmArrival::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'raw_material_uuid' => $detail['raw_material_uuid'],
                'production_code' => $detail['production_code'] ?? null,
                'time' => $detail['time'] ?? null,
                'temperature' => $detail['temperature'] ?? null,
                'packaging_condition' => $detail['packaging_condition'] ?? null,
                'sensorial_condition' => $detail['sensorial_condition'] ?? null,
                'problem' => $detail['problem'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
            ]);
        }

        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Pemeriksaan tambahan berhasil ditambahkan.');
    }

    public function known($id)
    {
        $report = ReportRmArrival::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve($id)
    {
        $report = ReportRmArrival::findOrFail($id);
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
        $report = ReportRmArrival::with([
            'area',
            'details.rawMaterial',
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

        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report_rm_arrivals.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_rm_arrival_' . $report->date . '.pdf');
    }

}