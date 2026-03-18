<?php

namespace App\Http\Controllers;

use App\Models\ReportSauce;
use App\Models\DetailSauce;
use App\Models\RmSauce;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Premix;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\SauceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportSauceController extends Controller
{

    public function index(Request $request)
    {
        $query = ReportSauce::with([
            'product',
            'area',
            'details.rawMaterials.rawMaterial',
            'details.rawMaterials.premix',
        ])->latest();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('production_code', 'like', "%{$search}%")
                ->orWhere('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('start_time', 'like', "%{$search}%")
                ->orWhere('end_time', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 PRODUCT
                $q->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%")
                    ->orWhere('production_code', 'like', "%{$search}%");
                });

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL SAUCE
                $q->orWhereHas('details', function ($d) use ($search) {

                    $d->where('time', 'like', "%{$search}%")
                    ->orWhere('process_step', 'like', "%{$search}%")
                    ->orWhere('duration', 'like', "%{$search}%")
                    ->orWhere('pressure', 'like', "%{$search}%")
                    ->orWhere('target_temperature', 'like', "%{$search}%")
                    ->orWhere('actual_temperature', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('aroma', 'like', "%{$search}%")
                    ->orWhere('taste', 'like', "%{$search}%")
                    ->orWhere('texture', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");

                    // 🔹 RAW MATERIAL DI DETAIL
                    $d->orWhereHas('rawMaterials', function ($rm) use ($search) {

                        $rm->where('amount', 'like', "%{$search}%")
                        ->orWhere('sensory', 'like', "%{$search}%");

                        // 🔹 RAW MATERIAL MASTER
                        $rm->orWhereHas('rawMaterial', function ($rmm) use ($search) {
                            $rmm->where('material_name', 'like', "%{$search}%");
                        });
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $totalKetidaksesuaian = 0;

            foreach ($report->details as $detail) {

                // 🔹 DETAIL SAUCE
                if (
                    $detail->color === 'Tidak OK' ||
                    $detail->aroma === 'Tidak OK' ||
                    $detail->taste === 'Tidak OK' ||
                    $detail->texture === 'Tidak OK'
                ) {
                    $totalKetidaksesuaian++;
                }

                // 🔹 RAW MATERIAL
                if ($detail->rawMaterials) {
                    $totalKetidaksesuaian += $detail->rawMaterials
                        ->filter(fn ($rm) => $rm->sensory === 'Tidak OK')
                        ->count();
                }
            }

            $report->ketidaksesuaian = $totalKetidaksesuaian;
            return $report;
        });

        return view('report_sauces.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $rawMaterials = RawMaterial::all();
        $premixes = Premix::orderBy('name')->get(); // tambah

        return view('report_sauces.create', compact('products', 'rawMaterials', 'premixes'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');
            // 1. Simpan HEADER laporan
            $report = ReportSauce::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
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
                            if (!empty($rm['material_uuid'])) {
                                $materialType = $rm['material_type'] ?? 'raw';

                                $detailModel->rawMaterials()->create([
                                    'uuid'             => Str::uuid(),
                                    'raw_material_uuid' => $materialType === 'raw' ? $rm['material_uuid'] : null,
                                    'material_uuid'    => $rm['material_uuid'],
                                    'material_type'    => $materialType,
                                    'amount'           => $rm['amount'] ?? null,
                                    'sensory'          => $rm['sensory'] ?? null,
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
            'details.rawMaterials.premix',
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
        $premixes = Premix::orderBy('name')->get(); // tambah

        return view('report_sauces.add_detail', compact('report', 'rawMaterials', 'premixes'));
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
                if (!empty($rm['material_uuid'])) {
                    $materialType = $rm['material_type'] ?? 'raw';

                    $detail->rawMaterials()->create([
                        'uuid'             => Str::uuid(),
                        'raw_material_uuid' => $materialType === 'raw' ? $rm['material_uuid'] : null,
                        'material_uuid'    => $rm['material_uuid'],
                        'material_type'    => $materialType,
                        'amount'           => $rm['amount'] ?? null,
                        'sensory'          => $rm['sensory'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('report_sauces.index')
            ->with('success', 'Detail berhasil ditambahkan');
    }

    public function edit($uuid)
    {
        $report = ReportSauce::with(['details.rawMaterials'])->where('uuid', $uuid)->firstOrFail();
        $products = Product::all();
        $rawMaterials = RawMaterial::all();
        $premixes = Premix::orderBy('name')->get(); // tambah

        return view('report_sauces.edit', compact('report', 'products', 'rawMaterials', 'premixes'));
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            $report = ReportSauce::where('uuid', $uuid)->firstOrFail();

            // ✅ Update header laporan
            $report->update([
                'date' => $request->date,
                'shift' => $request->shift,
                'product_uuid' => $request->product_uuid,
                'production_code' => $request->production_code,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'sensory' => $request->sensory,
            ]);

            // ✅ Hapus detail lama (biar tidak dobel data nested)
            $report->details()->each(function ($detail) {
                $detail->rawMaterials()->delete();
                $detail->delete();
            });

            // ✅ Simpan ulang detail baru
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

                    if (isset($detail['raw_materials'])) {
                        foreach ($detail['raw_materials'] as $rm) {
                            if (!empty($rm['material_uuid'])) {
                                $materialType = $rm['material_type'] ?? 'raw';

                                $detailModel->rawMaterials()->create([
                                    'uuid'              => Str::uuid(),
                                    'raw_material_uuid' => $materialType === 'raw' ? $rm['material_uuid'] : null,
                                    'material_uuid'     => $rm['material_uuid'],
                                    'material_type'     => $materialType,
                                    'amount'            => $rm['amount'] ?? null,
                                    'sensory'           => $rm['sensory'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('report_sauces.index')->with('success', 'Laporan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:range,month',
            'date_from'   => 'required_if:filter_type,range|nullable|date',
            'date_to'     => 'required_if:filter_type,range|nullable|date|after_or_equal:date_from',
            'month'       => 'required_if:filter_type,month|nullable|date_format:Y-m',
        ]);
    
        if ($request->filter_type === 'month') {
            $dateFrom    = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $dateTo      = $dateFrom->copy()->endOfMonth();
            $periodLabel = $dateFrom->translatedFormat('F Y');
        } else {
            $dateFrom    = Carbon::parse($request->date_from)->startOfDay();
            $dateTo      = Carbon::parse($request->date_to)->endOfDay();
            $periodLabel = $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y');
        }
    
        $reports = ReportSauce::with([
                'product',
                'details.rawMaterials.rawMaterial',
                'details.rawMaterials.premix',
            ])
            ->when(auth()->user()->hasRole('QC Inspector'), fn($q) =>
                $q->where('area_uuid', auth()->user()->area_uuid)
            )
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Sauce_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new SauceExport($reports, $periodLabel), $filename);
    }
}