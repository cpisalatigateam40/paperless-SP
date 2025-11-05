<?php

namespace App\Http\Controllers;

use App\Models\ReportBasoCooking;
use App\Models\DetailBasoCooking;
use App\Models\BasoTemperature;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportBasoCookingController extends Controller
{
    public function index()
    {
        $reports = ReportBasoCooking::with(['details.temperatures'])
            ->latest()
            ->paginate(10);

        // Tambahkan atribut ketidaksesuaian untuk setiap report
        $reports->getCollection()->transform(function ($report) {
            $totalKetidaksesuaian = 0;

            foreach ($report->details as $detail) {
                $fields = [
                    'sensory_shape',
                    'sensory_taste',
                    'sensory_aroma',
                    'sensory_texture',
                    'sensory_color',
                ];

                foreach ($fields as $field) {
                    // "0" dianggap Tidak OK
                    if (isset($detail->$field) && $detail->$field == '0') {
                        $totalKetidaksesuaian++;
                    }
                }
            }

            $report->ketidaksesuaian = $totalKetidaksesuaian;
            return $report;
        });

        return view('report_baso_cookings.index', compact('reports'));
    }


    public function create()
    {
        $products = Product::orderBy('product_name')->get();

        return view('report_baso_cookings.create', compact('products'));
    }

    private function saveSignature($base64Image, $prefix)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, dll

            $image = base64_decode($image);
            if ($image === false) {
                return null;
            }

            $fileName = $prefix . '_' . time() . '.' . $type;
            $filePath = 'baso/' . $fileName;

            if (!Storage::disk('public')->exists('baso')) {
                Storage::disk('public')->makeDirectory('baso');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }


    public function store(Request $request)
    {
        // Simpan header (report)
        $report = ReportBasoCooking::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid ?? null,
            'date' => $request->date,
            'shift' => $request->shift,
            'product_uuid' => $request->product_uuid,
            'std_core_temp' => $request->std_core_temp,
            'std_weight' => $request->std_weight,
            'set_boiling_1' => $request->set_boiling_1,
            'set_boiling_2' => $request->set_boiling_2,
            'created_by' => Auth::user()->name ?? 'system',
        ]);

        // Simpan detail
        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {

                // === Simpan paraf tanda tangan digital ===
                $qcParafPath = null;
                if (!empty($detail['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detail['prod_paraf'])) {
                    $productionParafPath = $this->saveSignature($detail['prod_paraf'], "prod_{$index}");
                }

                $detailModel = DetailBasoCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'production_code' => $detail['production_code'] ?? null,
                    'emulsion_temp' => $detail['emulsion_temp'] ?? null,
                    'boiling_tank_temp_1' => $detail['boiling_tank_temp_1'] ?? null,
                    'boiling_tank_temp_2' => $detail['boiling_tank_temp_2'] ?? null,
                    'initial_weight' => $detail['initial_weight'] ?? null,
                    'sensory_shape' => $detail['sensory_shape'] ?? null,
                    'sensory_taste' => $detail['sensory_taste'] ?? null,
                    'sensory_aroma' => $detail['sensory_aroma'] ?? null,
                    'sensory_texture' => $detail['sensory_texture'] ?? null,
                    'sensory_color' => $detail['sensory_color'] ?? null,
                    'final_weight' => $detail['final_weight'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'prod_paraf' => $productionParafPath,
                ]);

                // Simpan suhu (temperatures) per detail
                if (isset($detail['temperatures'])) {
                    foreach ($detail['temperatures'] as $temp) {
                        // Suhu awal
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'awal',
                            'time_recorded' => $temp['time_recorded'] ?? null,
                            'baso_temp_1' => $temp['baso_temp_1'] ?? null,
                            'baso_temp_2' => $temp['baso_temp_2'] ?? null,
                            'baso_temp_3' => $temp['baso_temp_3'] ?? null,
                            'baso_temp_4' => $temp['baso_temp_4'] ?? null,
                            'baso_temp_5' => $temp['baso_temp_5'] ?? null,
                            'avg_baso_temp' => $temp['avg_baso_temp'] ?? null,
                        ]);

                        // Suhu akhir (baris kosong, untuk diisi saat edit)
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'akhir',
                            'time_recorded' => null,
                            'baso_temp_1' => null,
                            'baso_temp_2' => null,
                            'baso_temp_3' => null,
                            'baso_temp_4' => null,
                            'baso_temp_5' => null,
                            'avg_baso_temp' => null,
                        ]);
                    }

                }
            }
        }

        return redirect()->route('report_baso_cookings.index')
            ->with('success', 'Laporan Baso Cooking berhasil disimpan');
    }

    public function destroy($uuid)
    {
        $report = ReportBasoCooking::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return back()->with('success', 'Laporan berhasil dihapus');
    }

    public function addDetail($reportUuid)
    {
        $report = ReportBasoCooking::where('uuid', $reportUuid)->firstOrFail();
        return view('report_baso_cookings.add_detail', compact('report'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportBasoCooking::where('uuid', $reportUuid)->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {

                // === Simpan paraf tanda tangan digital ===
                $qcParafPath = null;
                if (!empty($detail['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detail['prod_paraf'])) {
                    $productionParafPath = $this->saveSignature($detail['prod_paraf'], "prod_{$index}");
                }

                $detailModel = DetailBasoCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'production_code' => $detail['production_code'] ?? null,
                    'emulsion_temp' => $detail['emulsion_temp'] ?? null,
                    'boiling_tank_temp_1' => $detail['boiling_tank_temp_1'] ?? null,
                    'boiling_tank_temp_2' => $detail['boiling_tank_temp_2'] ?? null,
                    'initial_weight' => $detail['initial_weight'] ?? null,
                    'sensory_shape' => $detail['sensory_shape'] ?? null,
                    'sensory_taste' => $detail['sensory_taste'] ?? null,
                    'sensory_aroma' => $detail['sensory_aroma'] ?? null,
                    'sensory_texture' => $detail['sensory_texture'] ?? null,
                    'sensory_color' => $detail['sensory_color'] ?? null,
                    'final_weight' => $detail['final_weight'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'prod_paraf' => $productionParafPath,
                ]);

                // Simpan suhu (temperatures) per detail
                if (isset($detail['temperatures'])) {
                    foreach ($detail['temperatures'] as $temp) {
                        // Suhu awal
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'awal',
                            'time_recorded' => $temp['time_recorded'] ?? null,
                            'baso_temp_1' => $temp['baso_temp_1'] ?? null,
                            'baso_temp_2' => $temp['baso_temp_2'] ?? null,
                            'baso_temp_3' => $temp['baso_temp_3'] ?? null,
                            'baso_temp_4' => $temp['baso_temp_4'] ?? null,
                            'baso_temp_5' => $temp['baso_temp_5'] ?? null,
                            'avg_baso_temp' => $temp['avg_baso_temp'] ?? null,
                        ]);

                        // Suhu akhir (baris kosong, untuk diisi saat edit)
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'akhir',
                            'time_recorded' => null,
                            'baso_temp_1' => null,
                            'baso_temp_2' => null,
                            'baso_temp_3' => null,
                            'baso_temp_4' => null,
                            'baso_temp_5' => null,
                            'avg_baso_temp' => null,
                        ]);
                    }

                }

            }
        }

        return redirect()->route('report_baso_cookings.index')
            ->with('success', 'Detail tambahan berhasil disimpan');
    }

    public function exportPdf($uuid)
    {
        $report = ReportBasoCooking::with(['product', 'details.temperatures'])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_baso_cookings.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape'); // landscape biar tabel lebar

        return $pdf->stream('report-baso-cooking-' . $report->date . '.pdf');
    }

    public function known($id)
    {
        $report = ReportBasoCooking::findOrFail($id);
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
        $report = ReportBasoCooking::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function edit($uuid)
    {
        $report = ReportBasoCooking::with(['details.temperatures'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('report_baso_cookings.edit', compact('report'));
    }


    public function update(Request $request, $uuid)
    {
        $report = ReportBasoCooking::with(['details.temperatures'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                if (!empty($detail['temperatures'])) {
                    foreach ($detail['temperatures'] as $tempUuid => $tempData) {
                        $temperature = BasoTemperature::where('uuid', $tempUuid)->first();

                        // hanya update untuk 'akhir'
                        if ($temperature && $temperature->time_type === 'akhir') {
                            $temperature->update([
                                'baso_temp_1' => $tempData['baso_temp_1'] ?? $temperature->baso_temp_1,
                                'baso_temp_2' => $tempData['baso_temp_2'] ?? $temperature->baso_temp_2,
                                'baso_temp_3' => $tempData['baso_temp_3'] ?? $temperature->baso_temp_3,
                                'baso_temp_4' => $tempData['baso_temp_4'] ?? $temperature->baso_temp_4,
                                'baso_temp_5' => $tempData['baso_temp_5'] ?? $temperature->baso_temp_5,
                                'avg_baso_temp' => $tempData['avg_baso_temp'] ?? $temperature->avg_baso_temp,
                                'time_recorded' => $tempData['time_recorded'] ?? $temperature->time_recorded,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('report_baso_cookings.index')
            ->with('success', 'Laporan Baso Cooking berhasil diperbarui');
    }



}