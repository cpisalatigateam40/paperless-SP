<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportMetalDetector;
use App\Models\DetailMetalDetector;
use App\Models\Area;
use App\Models\Section;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportMetalDetectorController extends Controller
{
    public function index()
    {
        $reports = ReportMetalDetector::with(['area', 'section', 'details.product'])->latest()->get();
        return view('report_metal_detectors.index', compact('reports'));
    }

    // Form create
    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();

        return view('report_metal_detectors.create', compact('areas', 'sections', 'products'));
    }

    // Simpan report & detail
    public function store(Request $request)
    {
        // Validasi (minimal)
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required',
            'section_uuid' => 'nullable',
            'details' => 'required|array',
            'details.*.product_uuid' => 'required',
            'details.*.hour' => 'required',
            'details.*.production_code' => 'required',
            'details.*.notes' => 'nullable',
        ]);

        // Buat report
        $report = ReportMetalDetector::create([
            'uuid' => Str::uuid(),
            'date' => $request->date,
            'shift' => $request->shift,
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'created_by' => Auth::user()->name,
        ]);

        // Buat detail
        foreach ($request->details as $detail) {
            DetailMetalDetector::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'hour' => $detail['hour'],
                'production_code' => $detail['production_code'],
                'result_fe' => $detail['result_fe'],
                'result_non_fe' => $detail['result_non_fe'],
                'result_sus316' => $detail['result_sus316'],
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        return redirect()->route('report_metal_detectors.index')
            ->with('success', 'Report berhasil disimpan!');
    }

    public function destroy($id)
    {
        $report = ReportMetalDetector::findOrFail($id);
        $report->delete();

        return redirect()->route('report_metal_detectors.index')
            ->with('success', 'Report berhasil dihapus!');
    }

    public function addDetail($report_uuid)
    {
        $report = ReportMetalDetector::where('uuid', $report_uuid)->with(['area', 'section'])->firstOrFail();
        $products = Product::all();

        return view('report_metal_detectors.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'hour' => 'required',
            'production_code' => 'required',
            'result_fe' => 'required|in:√,x',
            'result_non_fe' => 'required|in:√,x',
            'result_sus316' => 'required|in:√,x',
            'notes' => 'nullable',
        ]);

        $report = ReportMetalDetector::where('uuid', $report_uuid)->firstOrFail();

        DetailMetalDetector::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'hour' => $request->hour,
            'production_code' => $request->production_code,
            'result_fe' => $request->result_fe,
            'result_non_fe' => $request->result_non_fe,
            'result_sus316' => $request->result_sus316,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_metal_detectors.index')->with('success', 'Detail berhasil ditambahkan!');
    }

    public function known($id)
    {
        $report = ReportMetalDetector::findOrFail($id);
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
        $report = ReportMetalDetector::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function exportPdf($report_uuid)
    {
        $report = ReportMetalDetector::where('uuid', $report_uuid)
            ->with(['details.product', 'area', 'section'])
            ->firstOrFail();

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

        $pdf = Pdf::loadView('report_metal_detectors.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('report-metal-detector-' . $report->date . '.pdf');
    }

}