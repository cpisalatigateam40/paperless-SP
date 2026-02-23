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
use Illuminate\Support\Facades\DB;

class ReportBasoCookingController extends Controller
{
    // public function index()
    // {
    //     $reports = ReportBasoCooking::with(['details.temperatures'])
    //         ->latest()
    //         ->paginate(10);

    //     // Tambahkan atribut ketidaksesuaian untuk setiap report
    //     $reports->getCollection()->transform(function ($report) {
    //         $totalKetidaksesuaian = 0;

    //         foreach ($report->details as $detail) {
    //             $fields = [
    //                 'sensory_shape',
    //                 'sensory_taste',
    //                 'sensory_aroma',
    //                 'sensory_texture',
    //                 'sensory_color',
    //             ];

    //             foreach ($fields as $field) {
    //                 // "0" dianggap Tidak OK
    //                 if (isset($detail->$field) && $detail->$field == '0') {
    //                     $totalKetidaksesuaian++;
    //                 }
    //             }
    //         }

    //         $report->ketidaksesuaian = $totalKetidaksesuaian;
    //         return $report;
    //     });

    //     return view('report_baso_cookings.index', compact('reports'));
    // }

    public function index(Request $request)
    {
        $query = ReportBasoCooking::with([
            'area',
            'product',
            'details.temperatures'
        ])->latest();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%")
                ->orWhere('std_core_temp', 'like', "%{$search}%")
                ->orWhere('std_weight', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 PRODUCT
                $q->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL BASO COOKING
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('production_code', 'like', "%{$search}%")
                    ->orWhere('emulsion_temp', 'like', "%{$search}%")
                    ->orWhere('boiling_tank_temp_1', 'like', "%{$search}%")
                    ->orWhere('boiling_tank_temp_2', 'like', "%{$search}%")
                    ->orWhere('initial_weight', 'like', "%{$search}%")
                    ->orWhere('final_weight', 'like', "%{$search}%")
                    ->orWhere('qc_paraf', 'like', "%{$search}%")
                    ->orWhere('prod_paraf', 'like', "%{$search}%")

                    // 🔹 SENSORY
                    ->orWhere('sensory_shape', 'like', "%{$search}%")
                    ->orWhere('sensory_taste', 'like', "%{$search}%")
                    ->orWhere('sensory_aroma', 'like', "%{$search}%")
                    ->orWhere('sensory_texture', 'like', "%{$search}%")
                    ->orWhere('sensory_color', 'like', "%{$search}%")

                    // 🔹 TEMPERATURE RECORD
                    ->orWhereHas('temperatures', function ($t) use ($search) {
                        $t->where('time_type', 'like', "%{$search}%")
                            ->orWhere('time_recorded', 'like', "%{$search}%")
                            ->orWhere('baso_temp_1', 'like', "%{$search}%")
                            ->orWhere('baso_temp_2', 'like', "%{$search}%")
                            ->orWhere('baso_temp_3', 'like', "%{$search}%")
                            ->orWhere('baso_temp_4', 'like', "%{$search}%")
                            ->orWhere('baso_temp_5', 'like', "%{$search}%")
                            ->orWhere('avg_baso_temp', 'like', "%{$search}%");
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $totalKetidaksesuaian = 0;

            foreach ($report->details as $detail) {
                foreach ([
                    'sensory_shape',
                    'sensory_taste',
                    'sensory_aroma',
                    'sensory_texture',
                    'sensory_color',
                ] as $field) {
                    // nilai "0" = Tidak OK
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
        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        // Simpan header (report)
        $report = ReportBasoCooking::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid ?? null,
            'date' => $request->date,
            'shift' => $shift,
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

    public function editNext($uuid)
    {
        $report = ReportBasoCooking::with(['details.temperatures'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $products = Product::orderBy('product_name')->get();

        // ambil details dari report agar view menemukan $details
        $details = $report->details;

        return view('report_baso_cookings.editNext', compact('report', 'products', 'details'));
    }


public function updateNext(Request $request, $uuid)
{
    // validasi dasar (opsional) bisa ditambah sesuai kebutuhan
    $request->validate([
        'date' => 'required|date',
        'shift' => 'required',
        // 'details' => 'required|array', // jika memang wajib
    ]);

    DB::beginTransaction();
    try {
        $report = ReportBasoCooking::with('details.temperatures')->where('uuid', $uuid)->firstOrFail();

        // 1) Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'product_uuid' => $request->product_uuid,
            'std_core_temp' => $request->std_core_temp,
            'std_weight' => $request->std_weight,
            'set_boiling_1' => $request->set_boiling_1,
            'set_boiling_2' => $request->set_boiling_2,
        ]);

        // 2) Cache data 'akhir' & paraf lama sebelum hapus
        $akhirMap = []; // keyed by production_code (lebih robust) or detail uuid if kamu kirim uuid
        $parafMap = []; // keyed by production_code -> ['qc_paraf' => ..., 'prod_paraf' => ...]

        foreach ($report->details as $existingDetail) {
            $prodCode = $existingDetail->production_code ?? null;

            // ambil suhu time_type = 'akhir' (ambil first jika ada)
            $akhir = $existingDetail->temperatures->firstWhere('time_type', 'akhir');
            if ($akhir) {
                $akhirMap[$prodCode] = [
                    'time_recorded' => $akhir->time_recorded,
                    'baso_temp_1' => $akhir->baso_temp_1,
                    'baso_temp_2' => $akhir->baso_temp_2,
                    'baso_temp_3' => $akhir->baso_temp_3,
                    'baso_temp_4' => $akhir->baso_temp_4,
                    'baso_temp_5' => $akhir->baso_temp_5,
                    'avg_baso_temp' => $akhir->avg_baso_temp,
                ];
            }

            //cache paraf jika perlu (agar tidak hilang)
            $parafMap[$prodCode] = [
                'qc_paraf' => $existingDetail->qc_paraf,
                'prod_paraf' => $existingDetail->prod_paraf,
            ];
        }

        // 3) Hapus detail + semua temperatur (kita akan recreate dari request)
        foreach ($report->details as $d) {
            $d->temperatures()->delete();
            $d->delete();
        }

        // 4) Recreate detail dari request, sambil memulihkan 'akhir' bila ada
        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {

                // Simpan paraf digital dari request bila ada; jika tidak ada, gunakan yang lama (jika ada)
                $qcParafPath = null;
                if (!empty($detail['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
                } elseif (!empty($parafMap[$detail['production_code']]['qc_paraf'])) {
                    $qcParafPath = $parafMap[$detail['production_code']]['qc_paraf'];
                }

                $productionParafPath = null;
                if (!empty($detail['prod_paraf'])) {
                    $productionParafPath = $this->saveSignature($detail['prod_paraf'], "prod_{$index}");
                } elseif (!empty($parafMap[$detail['production_code']]['prod_paraf'])) {
                    $productionParafPath = $parafMap[$detail['production_code']]['prod_paraf'];
                }

                $detailModel = DetailBasoCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'production_code' => $detail['production_code'] ?? null,
                    'emulsion_temp' => $detail['emulsion_temp'] ?? null,
                    'boiling_tank_temp_1' => $detail['boiling_tank_temp_1'] ?? null,
                    'boiling_tank_temp_2' => $detail['boiling_tank_temp_2'] ?? null,
                    'initial_weight' => $detail['initial_weight'] ?? null,
                    'final_weight' => $detail['final_weight'] ?? null,
                    'sensory_shape' => $detail['sensory_shape'] ?? null,
                    'sensory_taste' => $detail['sensory_taste'] ?? null,
                    'sensory_aroma' => $detail['sensory_aroma'] ?? null,
                    'sensory_texture' => $detail['sensory_texture'] ?? null,
                    'sensory_color' => $detail['sensory_color'] ?? null,
                    // 'qc_paraf' => $qcParafPath,
                    // 'prod_paraf' => $productionParafPath,
                ]);

                // Simpan suhu 'awal' dari request (jika ada)
                if (isset($detail['temperatures']) && is_array($detail['temperatures'])) {
                    foreach ($detail['temperatures'] as $temp) {
                        // buat record 'awal' berdasarkan data form
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

                        // restore 'akhir' jika ada cache untuk production_code
                        $prodCode = $detail['production_code'] ?? null;
                        if (isset($akhirMap[$prodCode])) {
                            $a = $akhirMap[$prodCode];
                            BasoTemperature::create([
                                'uuid' => Str::uuid(),
                                'detail_uuid' => $detailModel->uuid,
                                'time_type' => 'akhir',
                                'time_recorded' => $a['time_recorded'],
                                'baso_temp_1' => $a['baso_temp_1'],
                                'baso_temp_2' => $a['baso_temp_2'],
                                'baso_temp_3' => $a['baso_temp_3'],
                                'baso_temp_4' => $a['baso_temp_4'],
                                'baso_temp_5' => $a['baso_temp_5'],
                                'avg_baso_temp' => $a['avg_baso_temp'],
                            ]);
                        } else {
                            // jika tidak ada cache 'akhir', buat baris 'akhir' kosong (seperti saat create)
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
                } else {
                    // Jika tidak ada temperatures di request, tetap restore 'akhir' (tapi tanpa 'awal')
                    $prodCode = $detail['production_code'] ?? null;
                    if (isset($akhirMap[$prodCode])) {
                        $a = $akhirMap[$prodCode];
                        // buat baris 'awal' kosong supaya struktur tetap sama (opsional)
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'awal',
                            'time_recorded' => null,
                            'baso_temp_1' => null,
                            'baso_temp_2' => null,
                            'baso_temp_3' => null,
                            'baso_temp_4' => null,
                            'baso_temp_5' => null,
                            'avg_baso_temp' => null,
                        ]);
                        // restore akhir
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'akhir',
                            'time_recorded' => $a['time_recorded'],
                            'baso_temp_1' => $a['baso_temp_1'],
                            'baso_temp_2' => $a['baso_temp_2'],
                            'baso_temp_3' => $a['baso_temp_3'],
                            'baso_temp_4' => $a['baso_temp_4'],
                            'baso_temp_5' => $a['baso_temp_5'],
                            'avg_baso_temp' => $a['avg_baso_temp'],
                        ]);
                    } else {
                        // tidak ada temperatures request dan tidak ada cached akhir -> buat empty awal+akhir
                        BasoTemperature::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'time_type' => 'awal',
                            'time_recorded' => null,
                            'baso_temp_1' => null,
                            'baso_temp_2' => null,
                            'baso_temp_3' => null,
                            'baso_temp_4' => null,
                            'baso_temp_5' => null,
                            'avg_baso_temp' => null,
                        ]);
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

        DB::commit();
        return redirect()->route('report_baso_cookings.index')->with('success', 'Laporan Baso Cooking berhasil diperbarui.');
    } catch (\Throwable $e) {
        DB::rollBack();
        // untuk debug sementara bisa gunakan ->with('error', $e->getMessage())
        return back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
    }
}





}