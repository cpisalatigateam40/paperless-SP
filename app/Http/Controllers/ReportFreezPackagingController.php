<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReportFreezPackaging;
use App\Models\DetailFreezPackaging;
use App\Models\DataFreezing;
use App\Models\DataCartoning;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportFreezPackagingController extends Controller
{
    public function index()
    {
        $reports = ReportFreezPackaging::with(['area', 'details.kartoning'])->latest()->paginate(10);

        // Hitung ketidaksesuaian
        foreach ($reports as $report) {
            $count = 0;

            foreach ($report->details as $detail) {
                // 1️⃣ Cek verifikasi setelah tindakan koreksi
                if ($detail->verif_after === 'x') {
                    $count++;
                    continue; // tidak perlu cek karton jika sudah x
                }

                // 2️⃣ Cek kondisi karton
                if (optional($detail->kartoning)->carton_condition === 'x') {
                    $count++;
                }
            }

            // Tambahkan properti agar bisa dipakai di Blade
            $report->ketidaksesuaian = $count;
        }

        return view('report_freez_packagings.index', compact('reports'));
    }


    public function create()
    {
        $areas = Area::all();
        $products = Product::all();
        return view('report_freez_packagings.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {
        // dd($request->details);

        DB::beginTransaction();

        try {
            // Simpan header report
            $report = ReportFreezPackaging::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'created_by' => Auth::user()->name,
            ]);

            // Simpan detail, freezing, dan kartoning
            foreach ($request->details as $detail) {
                $detailModel = $report->details()->create([
                    'uuid' => Str::uuid(),
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'start_time' => $detail['start_time'] ?? null,
                    'end_time' => $detail['end_time'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verif_after' => $detail['verif_after'] ?? null,
                ]);

                $detailModel->freezing()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'start_product_temp' => $detail['freezing']['start_product_temp'] ?? null,
                    'standard_temp' => $detail['freezing']['standard_temp'] ?? null,
                    'end_product_temp' => $detail['freezing']['end_product_temp'] ?? null,
                    'iqf_room_temp' => $detail['freezing']['iqf_room_temp'] ?? null,
                    'iqf_suction_temp' => $detail['freezing']['iqf_suction_temp'] ?? null,
                    'freezing_time_display' => $detail['freezing']['freezing_time_display'] ?? null,
                    'freezing_time_actual' => $detail['freezing']['freezing_time_actual'] ?? null,
                ]);

                $detailModel->kartoning()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'carton_code' => $detail['kartoning']['carton_code'] ?? null,
                    'content_bag' => $detail['kartoning']['content_bag'] ?? null,
                    'content_binded' => $detail['kartoning']['content_binded'] ?? null,
                    'carton_weight_standard' => $detail['kartoning']['carton_weight_standard'] ?? null,
                    'carton_weight_actual' => $detail['kartoning']['carton_weight_actual'] ?? null,
                    'weight_1' => $detail['kartoning']['weight_1'] ?? null,
                    'weight_2' => $detail['kartoning']['weight_2'] ?? null,
                    'weight_3' => $detail['kartoning']['weight_3'] ?? null,
                    'weight_4' => $detail['kartoning']['weight_4'] ?? null,
                    'weight_5' => $detail['kartoning']['weight_5'] ?? null,
                    'avg_weight' => $detail['kartoning']['avg_weight'] ?? null,
                    'content_rtg' => $detail['kartoning']['content_rtg'] ?? null,
                    'carton_condition' => $detail['kartoning']['carton_condition'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('report_freez_packagings.index')->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report_freez_packagings.index')->with('success', 'Data berhasil dihapus');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();
        return view('report_freez_packagings.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $details = $request->input('details', []);

        foreach ($details as $item) {
            $detail = new DetailFreezPackaging([
                'report_uuid' => $report->uuid,
                'product_uuid' => $item['product_uuid'],
                'production_code' => $item['production_code'],
                'best_before' => $item['best_before'],
                'start_time' => now()->setTimeFromTimeString($item['start_time']),
                'end_time' => now()->setTimeFromTimeString($item['end_time']),
                'corrective_action' => $item['corrective_action'],
                'verif_after' => $item['verif_after'],
            ]);
            $detail->save();

            $detail->freezing()->create([
                // 'start_product_temp' => $item['freezing']['start_product_temp'],
                'end_product_temp' => $item['freezing']['end_product_temp'],
                'iqf_room_temp' => $item['freezing']['iqf_room_temp'],
                'standard_temp' => $item['freezing']['standard_temp'],
                'iqf_suction_temp' => $item['freezing']['iqf_suction_temp'],
                'freezing_time_display' => $item['freezing']['freezing_time_display'],
                'freezing_time_actual' => $item['freezing']['freezing_time_actual'],
            ]);

            $detail->kartoning()->create([
                // 'carton_code' => $item['kartoning']['carton_code'],
                'content_bag' => $item['kartoning']['content_bag'],
                'content_binded' => $item['kartoning']['content_binded'],
                'carton_weight_standard' => $item['kartoning']['carton_weight_standard'],
                // 'carton_weight_actual' => $item['kartoning']['carton_weight_actual'],
                'weight_1' => $item['kartoning']['weight_1'] ?? null,
                'weight_2' => $item['kartoning']['weight_2'] ?? null,
                'weight_3' => $item['kartoning']['weight_3'] ?? null,
                'weight_4' => $item['kartoning']['weight_4'] ?? null,
                'weight_5' => $item['kartoning']['weight_5'] ?? null,
                'avg_weight' => $item['kartoning']['avg_weight'] ?? null,
                'content_rtg' => $item['kartoning']['content_rtg'] ?? null,
                'carton_condition' => $item['kartoning']['carton_condition'] ?? null,
            ]);
        }

        return redirect()->route('report_freez_packagings.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportFreezPackaging::findOrFail($id);
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
        $report = ReportFreezPackaging::findOrFail($id);
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
        $report = ReportFreezPackaging::with([
            'area',
            'details.product',
            'details.freezing',
            'details.kartoning'
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

        $pdf = Pdf::loadView('report_freez_packagings.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper([0, 0, 1200, 595]);

        return $pdf->stream('laporan-pembekuan-kartoning.pdf');
    }

    public function edit($uuid)
{
    $report = ReportFreezPackaging::with([
        'details.freezing',
        'details.kartoning'
    ])->where('uuid', $uuid)->firstOrFail();

    $areas = Area::all();
    $products = Product::all();

    return view('report_freez_packagings.edit', compact('report', 'areas', 'products'));
}

public function update(Request $request, $uuid)
{
    $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();

    DB::beginTransaction();

    try {
        // Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        // Hapus detail lama beserta freezing & kartoning
        foreach ($report->details as $detail) {
            $detail->freezing()->delete();
            $detail->kartoning()->delete();
        }
        $report->details()->delete();

        // Simpan detail baru
        foreach ($request->details as $detail) {
            $detailModel = $report->details()->create([
                'uuid' => Str::uuid(),
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'best_before' => $detail['best_before'],
                'start_time' => $detail['start_time'] ?? null,
                'end_time' => $detail['end_time'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
                'verif_after' => $detail['verif_after'] ?? null,
            ]);

            $detailModel->freezing()->create([
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'start_product_temp' => $detail['freezing']['start_product_temp'] ?? null,
                'standard_temp' => $detail['freezing']['standard_temp'] ?? null,
                'end_product_temp' => $detail['freezing']['end_product_temp'] ?? null,
                'iqf_room_temp' => $detail['freezing']['iqf_room_temp'] ?? null,
                'iqf_suction_temp' => $detail['freezing']['iqf_suction_temp'] ?? null,
                'freezing_time_display' => $detail['freezing']['freezing_time_display'] ?? null,
                'freezing_time_actual' => $detail['freezing']['freezing_time_actual'] ?? null,
            ]);

            $detailModel->kartoning()->create([
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'carton_code' => $detail['kartoning']['carton_code'] ?? null,
                'content_bag' => $detail['kartoning']['content_bag'] ?? null,
                'content_binded' => $detail['kartoning']['content_binded'] ?? null,
                'carton_weight_standard' => $detail['kartoning']['carton_weight_standard'] ?? null,
                'carton_weight_actual' => $detail['kartoning']['carton_weight_actual'] ?? null,
                'weight_1' => $detail['kartoning']['weight_1'] ?? null,
                'weight_2' => $detail['kartoning']['weight_2'] ?? null,
                'weight_3' => $detail['kartoning']['weight_3'] ?? null,
                'weight_4' => $detail['kartoning']['weight_4'] ?? null,
                'weight_5' => $detail['kartoning']['weight_5'] ?? null,
                'avg_weight' => $detail['kartoning']['avg_weight'] ?? null,
                'content_rtg' => $detail['kartoning']['content_rtg'] ?? null,
                'carton_condition' => $detail['kartoning']['carton_condition'] ?? null,
            ]);
        }

        DB::commit();
        return redirect()->route('report_freez_packagings.index')->with('success', 'Data berhasil diperbarui');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    }
}
}