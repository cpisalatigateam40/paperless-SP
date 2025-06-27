<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportSharpTool;
use App\Models\DetailSharpTool;
use App\Models\SharpTool;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class ReportSharpToolController extends Controller
{
    public function index()
    {
        $reports = ReportSharpTool::with(['area', 'details.sharpTool'])->latest()->get();
        return view('report_sharp_tools.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sharpTools = SharpTool::with('area')->get();
        return view('report_sharp_tools.create', compact('areas', 'sharpTools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'details' => 'required|array',
            'details.*.sharp_tool_uuid' => 'required|exists:sharp_tools,uuid',
            'details.*.qty_start' => 'required|integer|min:0',
        ]);

        $report = ReportSharpTool::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $detail) {

            DetailSharpTool::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'sharp_tool_uuid' => $detail['sharp_tool_uuid'],
                'qty_start' => $detail['qty_start'],
                'qty_end' => $detail['qty_end'] ?? null,
                'check_time_1' => $detail['check_time_1'] ?? null,
                'condition_1' => $detail['condition_1'] ?? null,
                'check_time_2' => $detail['check_time_2'] ?? null,
                'condition_2' => $detail['condition_2'] ?? null,
                'note' => $detail['note'] ?? null,
            ]);
        }

        return redirect()->route('report_sharp_tools.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportSharpTool::where('uuid', $uuid)->firstOrFail();
        $report->details()->delete();
        $report->delete();

        return redirect()->route('report_sharp_tools.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function edit($uuid)
    {
        $report = ReportSharpTool::with(['details.sharpTool'])->where('uuid', $uuid)->firstOrFail();
        return view('report_sharp_tools.edit', compact('report'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'details' => 'required|array',
            'details.*.check_time_2' => 'nullable|date_format:H:i',
            'details.*.condition_2' => 'nullable|in:baik,rusak,hilang,tidaktersedia,-',
            'details.*.qty_end' => 'nullable|integer|min:0',
        ]);

        $report = ReportSharpTool::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details as $id => $input) {
            $detail = DetailSharpTool::where('id', $id)
                ->where('report_uuid', $report->uuid)
                ->first();

            if (!$detail)
                continue;

            $detail->update([
                'qty_end' => $input['qty_end'] ?? null,
                'check_time_2' => $input['check_time_2'] ?? null,
                'condition_2' => $input['condition_2'] ?? null,
                'note' => $input['note'] ?? null,
            ]);
        }

        return redirect()->route('report_sharp_tools.index')->with('success', 'Laporan berhasil diperbarui.');
    }


    public function addDetail($uuid)
    {
        $report = ReportSharpTool::with('details', 'area')->where('uuid', $uuid)->firstOrFail();
        $sharpTools = SharpTool::with('area')->get();

        return view('report_sharp_tools.add_detail', compact('report', 'sharpTools'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportSharpTool::where('uuid', $uuid)->firstOrFail();

        DB::beginTransaction();
        try {
            foreach ($request->details as $detail) {
                DetailSharpTool::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'sharp_tool_uuid' => $detail['sharp_tool_uuid'],
                    'qty_start' => $detail['qty_start'],
                    'qty_end' => null, // karena belum diisi
                    'check_time_1' => $detail['check_time_1'],
                    'condition_1' => $detail['condition_1'],
                    'check_time_2' => null,
                    'condition_2' => null,
                    'note' => $detail['note'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('report_sharp_tools.index')->with('success', 'Detail berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan detail: ' . $e->getMessage()])->withInput();
        }
    }

    public function approve($id)
    {
        $report = ReportSharptool::findOrFail($id);
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
        $report = ReportSharpTool::with(['area', 'details.sharpTool'])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_sharp_tools.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('a4', 'portrait');
        return $pdf->stream('laporan-pemeriksaan-benda-tajam-' . $report->date . '.pdf');
    }


}