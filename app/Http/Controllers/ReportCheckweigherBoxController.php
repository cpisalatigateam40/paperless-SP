<?php

namespace App\Http\Controllers;

use App\Models\ReportCheckweigherBox;
use App\Models\DetailCheckweigherBox;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportCheckweigherBoxController extends Controller
{
    public function index()
    {
        $reports = ReportCheckweigherBox::with('area', 'details.product')
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('report_checkweigher_boxes.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_checkweigher_boxes.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        // Simpan header report
        $report = ReportCheckweigherBox::create([
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail-detail
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $report->details()->create([
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'time_inspection' => $detail['time_inspection'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'expired_date' => $detail['expired_date'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'checkweigher_weight_gr' => $detail['checkweigher_weight_gr'] ?? null,
                    'manual_weight_gr' => $detail['manual_weight_gr'] ?? null,
                    'double_item' => !empty($detail['double_item']),
                    'weight_under' => !empty($detail['weight_under']),
                    'weight_over' => !empty($detail['weight_over']),
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('report_checkweigher_boxes.index')
            ->with('success', 'Data berhasil disimpan.');
    }


    public function destroy(string $uuid)
    {
        $report = ReportCheckweigherBox::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return back()->with('success', 'Report berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportCheckweigherBox::where('uuid', $uuid)->firstOrFail();
        $products = Product::orderBy('product_name')->get();

        return view('report_checkweigher_boxes.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportCheckweigherBox::where('uuid', $uuid)->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $report->details()->create([
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'time_inspection' => $detail['time_inspection'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'expired_date' => $detail['expired_date'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'checkweigher_weight_gr' => $detail['checkweigher_weight_gr'] ?? null,
                    'manual_weight_gr' => $detail['manual_weight_gr'] ?? null,
                    'double_item' => !empty($detail['double_item']),
                    'weight_under' => !empty($detail['weight_under']),
                    'weight_over' => !empty($detail['weight_over']),
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_checkweigher_boxes.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportCheckweigherBox::findOrFail($id);
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
        $report = ReportCheckweigherBox::with(['details.product'])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_checkweigher_boxes.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Laporan Checkweigher Box - ' . $report->date . '.pdf');
    }

}