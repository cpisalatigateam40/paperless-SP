<?php

namespace App\Http\Controllers;

use App\Models\ReportVacuumCondition;
use App\Models\DetailVacuumCondition;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportVacuumConditionController extends Controller
{
    public function index()
    {
        $reports = ReportVacuumCondition::with('area')->latest()->get();
        return view('report_vacuum_conditions.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_vacuum_conditions.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'details' => 'required|array|min:1',
            'details.*.product_uuid' => 'required|exists:products,uuid',
            'details.*.time' => 'required',
            'details.*.production_code' => 'required|string',
            'details.*.expired_date' => 'required|date',
            'details.*.pack_quantity' => 'required|integer',
        ]);

        $report_uuid = Str::uuid();

        // simpan header
        ReportVacuumCondition::create([
            'uuid' => $report_uuid,
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        // simpan detail
        foreach ($request->details as $detail) {
            DetailVacuumCondition::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report_uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'production_code' => $detail['production_code'],
                'expired_date' => $detail['expired_date'],
                'pack_quantity' => $detail['pack_quantity'],
                'leaking_area_seal' => isset($detail['leaking_area_seal']) ? 1 : 0,
                'leaking_area_melipat' => isset($detail['leaking_area_melipat']) ? 1 : 0,
                'leaking_area_casing' => isset($detail['leaking_area_casing']) ? 1 : 0,
                'leaking_area_other' => $detail['leaking_area_other'] ?? null,
            ]);
        }

        return redirect()->route('report_vacuum_conditions.index')
            ->with('success', 'Report dan detail berhasil disimpan.');
    }

    // Hapus data
    public function destroy($uuid)
    {
        $report = ReportVacuumCondition::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_vacuum_conditions.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportVacuumCondition::where('uuid', $report_uuid)->firstOrFail();
        $products = Product::all();
        return view('report_vacuum_conditions.details.create', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $report_uuid)
    {
        $report = ReportVacuumCondition::where('uuid', $report_uuid)->firstOrFail();

        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.product_uuid' => 'required|exists:products,uuid',
            'details.*.time' => 'required',
            'details.*.production_code' => 'required|string',
            'details.*.expired_date' => 'required|date',
            'details.*.pack_quantity' => 'required|integer',
        ]);

        foreach ($request->details as $detail) {
            DetailVacuumCondition::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'production_code' => $detail['production_code'],
                'expired_date' => $detail['expired_date'],
                'pack_quantity' => $detail['pack_quantity'],
                'leaking_area_seal' => isset($detail['leaking_area_seal']) ? 1 : 0,
                'leaking_area_melipat' => isset($detail['leaking_area_melipat']) ? 1 : 0,
                'leaking_area_casing' => isset($detail['leaking_area_casing']) ? 1 : 0,
                'leaking_area_other' => $detail['leaking_area_other'] ?? null,
            ]);
        }

        return redirect()->route('report_vacuum_conditions.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }

    public function destroyDetail($id)
    {
        $detail = DetailVacuumCondition::findOrFail($id);
        $detail->delete();

        return back()->with('success', 'Detail berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportVacuumCondition::findOrFail($id);
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
        $report = ReportVacuumCondition::with(['area', 'details.product'])
            ->where('uuid', $uuid)
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

        $pdf = Pdf::loadView('report_vacuum_conditions.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('report-vacuum-condition-' . $report->date . '.pdf');
    }
}