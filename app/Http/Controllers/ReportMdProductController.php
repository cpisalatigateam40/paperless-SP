<?php

namespace App\Http\Controllers;

use App\Models\ReportMdProduct;
use App\Models\DetailMdProduct;
use App\Models\PositionMdProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportMdProductController extends Controller
{
    public function index()
    {
        $reports = ReportMdProduct::latest()->paginate(10);
        return view('report_md_products.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        return view('report_md_products.create', compact('products'));
    }


    public function store(Request $request)
    {
        // Validasi minimal header, sesuaikan sesuai kebutuhan
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
        ]);

        // Simpan header report
        $report = ReportMdProduct::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail jika ada
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $detailModel = DetailMdProduct::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'best_before' => $detail['best_before'] ?? null,
                    'time' => $detail['time'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => isset($detail['verification']) ? (bool) $detail['verification'] : false,
                ]);


                // Simpan posisi jika ada
                if (!empty($detail['positions'])) {
                    foreach ($detail['positions'] as $position) {
                        PositionMdProduct::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'specimen' => $position['specimen'] ?? null,
                            'position' => $position['position'] ?? null,
                            'status' => isset($position['status']) ? (bool) $position['status'] : false,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('report_md_products.index')
            ->with('success', 'Report berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_md_products.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
        $products = \App\Models\Product::all();
        return view('report_md_products.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();

        if ($request->details) {
            foreach ($request->details as $detail) {
                $detailModel = DetailMdProduct::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'best_before' => $detail['best_before'] ?? null,
                    'time' => $detail['time'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => isset($detail['verification']) ? (bool) $detail['verification'] : false,
                ]);

                if (!empty($detail['positions'])) {
                    foreach ($detail['positions'] as $position) {
                        \App\Models\PositionMdProduct::create([
                            'uuid' => \Illuminate\Support\Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'specimen' => $position['specimen'] ?? null,
                            'position' => $position['position'] ?? null,
                            'status' => isset($position['status']) ? (bool) $position['status'] : false,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('report_md_products.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportMdProduct::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function known($id)
    {
        $report = ReportMdProduct::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)
            ->with(['details.positions', 'details.product'])
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

        $pdf = PDF::loadView('report_md_products.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);
        return $pdf->stream('Report-Metal-Detector-' . $report->date . '.pdf');
    }

}