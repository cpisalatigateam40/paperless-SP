<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ReportRetainSample;
use App\Models\DetailRetainSample;
use App\Models\Area;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRetainSampleController extends Controller
{
    public function index()
    {
        $reports = ReportRetainSample::with('details')->latest()->get();
        return view('report_retain_samples.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_retain_samples.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        $report = ReportRetainSample::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail produk retain
        foreach ($request->details ?? [] as $detail) {
            DetailRetainSample::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'] ?? null,
                'production_code' => $detail['production_code'] ?? null,
                'room_temp' => $detail['room_temp'] ?? null,
                'suction_temp' => $detail['suction_temp'] ?? null,
                'display_speed' => $detail['display_speed'] ?? null,
                'actual_speed' => $detail['actual_speed'] ?? null,
                'time_in' => $detail['time_in'] ?? null,
                'line_type' => $detail['line_type'] ?? null,
                'signature_in' => Auth::user()->name ?? 'Unknown',
                'signature_out' => Auth::user()->name ?? 'Unknown',
            ]);
        }

        return redirect()->route('report_retain_samples.index')->with('success', 'Laporan berhasil disimpan.');
    }


    public function destroy($uuid)
    {
        $report = ReportRetainSample::where('uuid', $uuid)->firstOrFail();
        $report->details()->delete();
        $report->delete();

        return redirect()->route('report_retain_samples.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportRetainSample::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();

        return view('report_retain_samples.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        // dd($request->all());

        $report = ReportRetainSample::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details ?? [] as $detail) {
            foreach ($detail['products'] ?? [] as $product) {
                DetailRetainSample::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $product['product_uuid'] ?? null,
                    'production_code' => $product['production_code'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'suction_temp' => $detail['suction_temp'] ?? null,
                    'display_speed' => $detail['display_speed'] ?? null,
                    'actual_speed' => $detail['actual_speed'] ?? null,
                    'time_in' => $detail['time_in'] ?? null,
                    'line_type' => $detail['line_type'] ?? null,
                    'signature_in' => Auth::user()->name ?? 'Unknown',
                    'signature_out' => Auth::user()->name ?? 'Unknown',
                ]);
            }
        }

        return redirect()->route('report_retain_samples.index')->with('success', 'Detail berhasil ditambahkan.');
    }


    public function approve($id)
    {
        $report = ReportRetainSample::findOrFail($id);
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
        $report = ReportRetainSample::findOrFail($id);
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
        $report = ReportRetainSample::with('details.product')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_retain_samples.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan Retain Sample - ' . $report->date . '.pdf');
    }
}