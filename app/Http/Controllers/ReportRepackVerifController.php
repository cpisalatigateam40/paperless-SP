<?php

namespace App\Http\Controllers;

use App\Models\ReportRepackVerif;
use App\Models\DetailRepackVerif;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRepackVerifController extends Controller
{
    public function index()
    {
        $reports = ReportRepackVerif::with(['area', 'details.product'])->latest()->get();
        return view('report_repack_verifs.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::all(['uuid', 'product_name', 'shelf_life', 'created_at']);
        return view('report_repack_verifs.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'details' => 'required|array',
            'details.*.product_uuid' => 'required|exists:products,uuid',
            'details.*.production_code' => 'nullable|string',
            'details.*.expired_date' => 'nullable|date',
            'details.*.reason' => 'nullable|string',
            'details.*.notes' => 'nullable|string',
        ]);

        $report = ReportRepackVerif::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by ?? null,
            'approved_by' => $request->approved_by ?? null,
        ]);

        foreach ($request->details as $detail) {
            DetailRepackVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'expired_date' => $detail['expired_date'],
                'reason' => $detail['reason'],
                'notes' => $detail['notes'],
            ]);
        }

        return redirect()->route('report_repack_verifs.index')->with('success', 'Report created successfully.');
    }

    public function destroy($id)
    {
        $report = ReportRepackVerif::findOrFail($id);
        $report->delete();
        return redirect()->route('report_repack_verifs.index')->with('success', 'Report deleted successfully.');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportRepackVerif::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $products = Product::all(['uuid', 'product_name', 'shelf_life', 'created_at']);
        return view('report_repack_verifs.add_detail', compact('report', 'products'));
    }

    // Store detail
    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required|exists:products,uuid',
            'production_code' => 'nullable|string',
            'expired_date' => 'nullable|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $report = ReportRepackVerif::where('uuid', $report_uuid)->firstOrFail();

        DetailRepackVerif::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'expired_date' => $request->expired_date,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_repack_verifs.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportRepackVerif::findOrFail($id);
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
        $report = ReportRepackVerif::with(['area', 'details.product'])
            ->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_repack_verifs.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Report_Repack_' . $report->date . '.pdf');
    }
}