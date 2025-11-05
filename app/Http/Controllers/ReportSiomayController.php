<?php

namespace App\Http\Controllers;

use App\Models\ReportSiomay;
use App\Models\DetailSiomay;
use App\Models\RmSiomay;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportSiomayController extends Controller
{
    public function index()
    {
        $reports = ReportSiomay::with([
            'product',
            'area',
            'details.rawMaterials.rawMaterial',
        ])->latest()->paginate(10);

        // transform() agar tetap bekerja dengan pagination
        $reports->getCollection()->transform(function ($report) {
            $totalKetidaksesuaian = 0;

            foreach ($report->details as $detail) {
                // 🔹 Cek di level detail proses (color, aroma, taste, texture)
                if (
                    $detail->color === 'Tidak OK' ||
                    $detail->aroma === 'Tidak OK' ||
                    $detail->taste === 'Tidak OK' ||
                    $detail->texture === 'Tidak OK'
                ) {
                    $totalKetidaksesuaian++;
                }

                // 🔹 Cek di level bahan baku (raw materials)
                if ($detail->rawMaterials) {
                    $totalKetidaksesuaian += $detail->rawMaterials
                        ->filter(fn($rm) => $rm->sensory === 'Tidak OK')
                        ->count();
                }
            }

            $report->ketidaksesuaian = $totalKetidaksesuaian;

            return $report;
        });

        return view('report_siomays.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $rawMaterials = RawMaterial::all();
        return view('report_siomays.create', compact('products', 'rawMaterials'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan HEADER laporan
            $report = ReportSiomay::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'product_uuid' => $request->product_uuid,
                'production_code' => $request->production_code,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'sensory' => $request->sensory,
                'created_by' => Auth::user()->name,
            ]);

            // 2. Simpan DETAIL proses + raw materials per detail
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $detailModel = $report->details()->create([
                        'uuid' => Str::uuid(),
                        'time' => $detail['time'] ?? null,
                        'process_step' => $detail['process_step'] ?? null,
                        'duration' => $detail['duration'] ?? null,
                        'pressure' => $detail['pressure'] ?? null,
                        'target_temperature' => $detail['target_temperature'] ?? null,
                        'actual_temperature' => $detail['actual_temperature'] ?? null,
                        'color' => $detail['color'] ?? null,
                        'aroma' => $detail['aroma'] ?? null,
                        'taste' => $detail['taste'] ?? null,
                        'texture' => $detail['texture'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'mixing_paddle_on' => isset($detail['mixing_paddle']) && $detail['mixing_paddle'] === 'on',
                        'mixing_paddle_off' => isset($detail['mixing_paddle']) && $detail['mixing_paddle'] === 'off',
                    ]);

                    // simpan raw materials untuk detail ini
                    if (isset($detail['raw_materials'])) {
                        foreach ($detail['raw_materials'] as $rm) {
                            if (!empty($rm['raw_material_uuid'])) {
                                $detailModel->rawMaterials()->create([
                                    'uuid' => Str::uuid(),
                                    'raw_material_uuid' => $rm['raw_material_uuid'],
                                    'amount' => $rm['amount'] ?? null,
                                    'sensory' => $rm['sensory'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('report_siomays.index')->with('success', 'Laporan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($uuid)
    {
        $report = ReportSiomay::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_siomays.index')->with('success', 'Laporan berhasil dihapus');
    }

    public function known($id)
    {
        $report = ReportSiomay::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve($id)
    {
        $report = ReportSiomay::findOrFail($id);
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
        $report = ReportSiomay::with([
            'product',
            'area',
            'details.rawMaterials.rawMaterial',
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

        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $pdf = Pdf::loadView('report_siomays.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape');

        return $pdf->stream('report_siomay_' . $report->uuid . '.pdf');
    }

    public function addDetail($reportUuid)
    {
        $report = ReportSiomay::where('uuid', $reportUuid)->firstOrFail();
        $rawMaterials = RawMaterial::all();

        return view('report_siomays.add_detail', compact('report', 'rawMaterials'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportSiomay::where('uuid', $reportUuid)->firstOrFail();

        $detailData = $request->input('details')[0];

        $detail = new DetailSiomay();
        $detail->uuid = Str::uuid();
        $detail->report_uuid = $report->uuid;
        $detail->time = $detailData['time'] ?? null;
        $detail->process_step = $detailData['process_step'] ?? null;
        $detail->duration = $detailData['duration'] ?? null;
        $detail->pressure = $detailData['pressure'] ?? null;
        $detail->target_temperature = $detailData['target_temperature'] ?? null;
        $detail->actual_temperature = $detailData['actual_temperature'] ?? null;

        if (isset($detailData['mixing_paddle'])) {
            if ($detailData['mixing_paddle'] === 'on') {
                $detail->mixing_paddle_on = 1;
                $detail->mixing_paddle_off = 0;
            } else {
                $detail->mixing_paddle_on = 0;
                $detail->mixing_paddle_off = 1;
            }
        }

        $detail->color = $detailData['color'] ?? null;
        $detail->aroma = $detailData['aroma'] ?? null;
        $detail->taste = $detailData['taste'] ?? null;
        $detail->texture = $detailData['texture'] ?? null;
        $detail->notes = $detailData['notes'] ?? null;
        $detail->save();

        if (!empty($detailData['raw_materials'])) {
            foreach ($detailData['raw_materials'] as $rm) {
                if (!empty($rm['raw_material_uuid'])) {
                    $detail->rawMaterials()->create([
                        'uuid' => Str::uuid(),
                        'raw_material_uuid' => $rm['raw_material_uuid'],
                        'amount' => $rm['amount'] ?? null,
                        'sensory' => $rm['sensory'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('report_siomays.index')
            ->with('success', 'Detail berhasil ditambahkan');
    }
}