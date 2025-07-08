<?php

namespace App\Http\Controllers;

use App\Models\ReportReturn;
use App\Models\DetailReturn;
use App\Models\Area;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportReturnController extends Controller
{
    // GET /report-returns → report_returns.index
    public function index()
    {
        $reports = ReportReturn::with('area', 'details.rawMaterial')->latest()->get();
        return view('report_returns.index', compact('reports'));
    }

    // GET /report-returns/create → report_returns.create
    public function create()
    {
        $areas = Area::all();
        $rawMaterials = RawMaterial::all();
        return view('report_returns.create', compact('areas', 'rawMaterials'));
    }

    // POST /report-returns → report_returns.store
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $report = ReportReturn::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailReturn::create([
                    'report_uuid' => $report->uuid,
                    'rm_uuid' => $detail['rm_uuid'],
                    'supplier' => $detail['supplier'],
                    'production_code' => $detail['production_code'],
                    'hold_reason' => $detail['hold_reason'],
                    'quantity' => $detail['quantity'],
                    'unit' => $detail['unit'],
                    'action' => $detail['action'],
                ]);
            }
        });

        return redirect()->route('report_returns.index')->with('success', 'Report berhasil disimpan.');
    }

    // DELETE /report-returns/{uuid} → report_returns.destroy
    public function destroy($uuid)
    {
        $report = ReportReturn::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return back()->with('success', 'Report berhasil dihapus.');
    }

    // POST /report-returns/{uuid}/approve → report_returns.approve
    public function approve($id)
    {
        $report = ReportReturn::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    // GET /report-returns/{uuid}/export-pdf → report_returns.export_pdf
    public function exportPdf($uuid)
    {
        $report = ReportReturn::where('uuid', $uuid)
            ->with('details.rawMaterial', 'area')
            ->firstOrFail();

        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQr = 'data:image/png;base64,' . base64_encode(QrCode::format('png')->size(150)->generate($createdInfo));

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQr = 'data:image/png;base64,' . base64_encode(QrCode::format('png')->size(150)->generate($approvedInfo));

        $pdf = Pdf::loadView('report_returns.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQr,
            'approvedQr' => $approvedQr,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('ReportReturn-' . $report->date . '.pdf');
    }

    // GET /report-returns/{report_uuid}/add-detail → report_returns.details.create
    public function createDetail($report_uuid)
    {
        $report = ReportReturn::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $rawMaterials = RawMaterial::all();
        return view('report_returns.add_detail', compact('report', 'rawMaterials'));
    }

    // POST /report-returns/{report_uuid}/add-detail → report_returns.details.store
    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'rm_uuid' => 'required',
            'supplier' => 'required',
            'production_code' => 'required',
            'quantity' => 'required|integer',
            'hold_reason' => 'nullable|string',
            'action' => 'nullable|string',
        ]);

        $report = ReportReturn::where('uuid', $report_uuid)->firstOrFail();

        DetailReturn::create([
            'report_uuid' => $report->uuid,
            'rm_uuid' => $request->rm_uuid,
            'supplier' => $request->supplier,
            'production_code' => $request->production_code,
            'hold_reason' => $request->hold_reason,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'action' => $request->action,
        ]);

        return redirect()->route('report_returns.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    // DELETE /report-returns/details/{id} → report_returns.details.destroy
    public function destroyDetail($id)
    {
        $detail = DetailReturn::findOrFail($id);
        $detail->delete();

        return back()->with('success', 'Detail berhasil dihapus.');
    }
}