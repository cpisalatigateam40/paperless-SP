<?php

namespace App\Http\Controllers;

use App\Models\ReportLabSample;
use App\Models\Area;
use App\Models\Product;
use App\Models\DetailLabSample;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportLabSampleController extends Controller
{
    public function index()
    {
        $reports = ReportLabSample::with('area', 'details.product')->latest()->get();
        return view('report_lab_samples.index', compact('reports'));
    }

    // Form create
    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_lab_samples.create', compact('areas', 'products'));
    }

    // Store data
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $report = ReportLabSample::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'storage' => implode(', ', $request->storage ?? []),
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailLabSample::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'quantity' => $detail['quantity'],
                    'notes' => $detail['notes'],
                ]);
            }
        });

        return redirect()->route('report_lab_samples.index')->with('success', 'Data berhasil disimpan');
    }

    // Delete
    public function destroy($id)
    {
        $report = ReportLabSample::findOrFail($id);
        $report->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportLabSample::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $products = Product::all();
        return view('report_lab_samples.add_detail', compact('report', 'products'));
    }

    // Simpan detail baru
    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'production_code' => 'required',
            'best_before' => 'required|date',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $report = ReportLabSample::where('uuid', $report_uuid)->firstOrFail();

        DetailLabSample::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'best_before' => $request->best_before,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_lab_samples.index')->with('success', 'Detail berhasil ditambahkan');
    }

    public function approve($id)
    {
        $report = ReportLabSample::findOrFail($id);
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
        $report = ReportLabSample::findOrFail($id);
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
        $report = ReportLabSample::where('uuid', $uuid)
            ->with('details.product', 'area')
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

        $pdf = Pdf::loadView('report_lab_samples.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('ReportLabSample-' . $report->date . '.pdf');
    }
}