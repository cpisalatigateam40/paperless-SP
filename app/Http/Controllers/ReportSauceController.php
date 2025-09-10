<?php

namespace App\Http\Controllers;

use App\Models\ReportSauce;
use App\Models\DetailSauce;
use App\Models\RmSauce;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportSauceController extends Controller
{
    public function index()
    {
        $reports = ReportSauce::with([
            'product',
            'area',
            'details.rawMaterials.rawMaterial',
        ])->latest()->get();

        return view('report_sauces.index', compact('reports'));
    }


    // 2. Create: tampilkan form tambah
    public function create()
    {
        $products = Product::all();
        $rawMaterials = RawMaterial::all();
        return view('report_sauces.create', compact('products', 'rawMaterials'));
    }

    // 3. Store: simpan data header + detail + raw material
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan HEADER laporan
            $report = ReportSauce::create([
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
            return redirect()->route('report_sauces.index')->with('success', 'Laporan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    // 4. Destroy: hapus laporan + relasi
    public function destroy($uuid)
    {
        $report = ReportSauce::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_sauces.index')->with('success', 'Laporan berhasil dihapus');
    }

    public function known($id)
    {
        $report = ReportSauce::findOrFail($id);
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
        $report = ReportSauce::findOrFail($id);
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
        $report = ReportSauce::with([
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

        $pdf = Pdf::loadView('report_sauces.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape');

        return $pdf->stream('report_sauce_' . $report->uuid . '.pdf');
    }

    // Form tambah detail
    public function addDetail($reportUuid)
    {
        $report = ReportSauce::where('uuid', $reportUuid)->firstOrFail();
        $rawMaterials = RawMaterial::all();

        return view('report_sauces.add_detail', compact('report', 'rawMaterials'));
    }

    // Simpan detail baru
    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportSauce::where('uuid', $reportUuid)->firstOrFail();

        // Ambil 1 detail (karena form add-detail hanya kirim 1 detail per kali submit)
        $detailData = $request->input('details')[0];

        $detail = new DetailSauce();
        $detail->uuid = Str::uuid();
        $detail->report_uuid = $report->uuid;
        $detail->time = $detailData['time'] ?? null;
        $detail->process_step = $detailData['process_step'] ?? null;
        $detail->duration = $detailData['duration'] ?? null;
        $detail->pressure = $detailData['pressure'] ?? null;
        $detail->target_temperature = $detailData['target_temperature'] ?? null;
        $detail->actual_temperature = $detailData['actual_temperature'] ?? null;

        // Mixing paddle (radio on/off)
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

        // ✅ Simpan raw materials ke tabel rm_sauces lewat detail_uuid
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

        return redirect()->route('report_sauces.index')
            ->with('success', 'Detail berhasil ditambahkan');
    }


}