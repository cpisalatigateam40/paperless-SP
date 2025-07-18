<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportProductVerif;
use App\Models\DetailProductVerif;
use App\Models\ProductVerifMeasurement;
use App\Models\Product;
use App\Models\Area;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportProductVerifController extends Controller
{
    public function index()
    {
        $reports = ReportProductVerif::with('details.product')->latest()->get();
        return view('report_product_verifs.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();
        return view('report_product_verifs.create', compact('products', 'areas'));
    }

    public function store(Request $request)
    {
        $report = ReportProductVerif::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail produk
        foreach ($request->details ?? [] as $detail) {
            $detailModel = DetailProductVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'] ?? null,
                'jam' => $detail['jam'] ?? null,
                'production_code' => $detail['production_code'] ?? null,
                'expired_date' => $detail['expired_date'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
                'weight_standard' => $detail['weight_standard'] ?? null,
                'diameter_standard' => $detail['diameter_standard'] ?? null,
            ]);

            // Simpan 5 baris pengukuran aktual
            foreach ($detail['measurements'] ?? [] as $m) {
                ProductVerifMeasurement::create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'sequence' => $m['sequence'] ?? null,
                    'length_actual' => $m['length_actual'] ?? null,
                    'weight_actual' => $m['weight_actual'] ?? null,
                    'diameter_actual' => $m['diameter_actual'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_product_verifs.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportProductVerif::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportProductVerif::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();

        return view('report_product_verifs.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportProductVerif::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details ?? [] as $detail) {
            $detailModel = DetailProductVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'] ?? null,
                'jam' => $detail['jam'] ?? null,
                'production_code' => $detail['production_code'] ?? null,
                'expired_date' => $detail['expired_date'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
                'weight_standard' => $detail['weight_standard'] ?? null,
                'diameter_standard' => $detail['diameter_standard'] ?? null,
            ]);

            foreach ($detail['measurements'] ?? [] as $m) {
                ProductVerifMeasurement::create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'sequence' => $m['sequence'] ?? null,
                    'length_actual' => $m['length_actual'] ?? null,
                    'weight_actual' => $m['weight_actual'] ?? null,
                    'diameter_actual' => $m['diameter_actual'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_product_verifs.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportProductVerif::findOrFail($id);
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
        $report = ReportProductVerif::with([
            'details.product',
            'details.measurements' => fn($q) => $q->orderBy('sequence'),
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

        $pdf = Pdf::loadView('report_product_verifs.export-pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan Verifikasi Produk - ' . $report->date . '.pdf');
    }
}