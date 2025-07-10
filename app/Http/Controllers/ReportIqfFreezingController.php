<?php

namespace App\Http\Controllers;

use App\Models\ReportIqfFreezing;
use App\Models\DetailIqfFreezing;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportIqfFreezingController extends Controller
{
    public function index()
    {
        $reports = ReportIqfFreezing::with('area', 'details.product')->latest()->get();
        return view('report_iqf_freezings.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_iqf_freezings.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $report = ReportIqfFreezing::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => getShift(),
                'created_by' => Auth::user()->name,
            ]);

            if ($request->details) {
                foreach ($request->details as $detail) {
                    DetailIqfFreezing::create([
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'product_uuid' => $detail['product_uuid'],
                        'production_code' => $detail['production_code'],
                        'best_before' => $detail['best_before'],
                        'product_temp_before_iqf' => is_numeric($detail['product_temp_before_iqf']) ? $detail['product_temp_before_iqf'] : null,
                        'freezing_start_time' => $detail['freezing_start_time'],
                        'freezing_duration' => $detail['freezing_duration'],
                        'room_temperature' => $detail['room_temperature'],
                        'suction_temperature' => $detail['suction_temperature'],
                    ]);
                }
            }
        });

        return redirect()->route('report_iqf_freezings.index')->with('success', 'Report berhasil disimpan');
    }

    public function destroy($id)
    {
        $report = ReportIqfFreezing::findOrFail($id);
        $report->delete();
        return back()->with('success', 'Report berhasil dihapus');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportIqfFreezing::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $products = Product::all();
        return view('report_iqf_freezings.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'production_code' => 'required',
            'best_before' => 'required|date',
            'product_temp_before_iqf' => 'nullable|numeric',
            'freezing_start_time' => 'nullable',
            'freezing_duration' => 'nullable|integer',
            'room_temperature' => 'nullable|string',
            'suction_temperature' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $report = ReportIqfFreezing::where('uuid', $report_uuid)->firstOrFail();

        DetailIqfFreezing::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'best_before' => $request->best_before,
            'product_temp_before_iqf' => $request->product_temp_before_iqf,
            'freezing_start_time' => $request->freezing_start_time,
            'freezing_duration' => $request->freezing_duration,
            'room_temperature' => $request->room_temperature,
            'suction_temperature' => $request->suction_temperature,
            'note' => $request->note,
        ]);

        return redirect()->route('report_iqf_freezings.index')->with('success', 'Detail berhasil ditambahkan');
    }

    public function exportPdf($uuid)
    {
        $report = ReportIqfFreezing::where('uuid', $uuid)
            ->with('details.product', 'area')
            ->firstOrFail();

        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $pdf = Pdf::loadView('report_iqf_freezings.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('ReportIqfFreezing-' . $report->date . '.pdf');
    }

    public function approve($id)
    {
        $report = ReportIqfFreezing::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }
}