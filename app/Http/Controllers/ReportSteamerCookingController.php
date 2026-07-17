<?php

namespace App\Http\Controllers;

use App\Models\ReportSteamerCooking;
use App\Models\SteamerCookingBatch;
use App\Models\SteamerCookingDetail;
use App\Models\SteamerCookingCoreTemp;
use App\Models\SteamerStandard;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use Carbon\Carbon;
use App\Exports\SteamerCookingExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportSteamerCookingController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport;
    protected string $bulkModel = ReportSteamerCooking::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportSteamerCooking::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_steamer_cookings.export_pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['creator',
            'product',
            'area',
            'batches.details.coreTemps',];
    }

    protected function getBulkExportExtraData($report): array
    {
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQr = QrCode::format('png')->size(150)->generate($createdInfo);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQr = QrCode::format('png')->size(150)->generate($approvedInfo);

        $knownInfo = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
        $knownQr = QrCode::format('png')->size(150)->generate($knownInfo);

        return [
            'createdQr'  => 'data:image/png;base64,' . base64_encode($createdQr),
            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQr),
            'knownQr'    => 'data:image/png;base64,' . base64_encode($knownQr),
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_steamer';
    }

    public function index(Request $request)
    {
        $query = ReportSteamerCooking::with(['product', 'area', 'batches.details.coreTemps'])
            ->withCount('batches');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $reports = $query->latest()->paginate(20);

        return view('report_steamer_cookings.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('report_steamer_cookings.create', compact('products'));
    }

    public function getStandard($product_uuid)
    {
        $standard = SteamerStandard::where('product_uuid', $product_uuid)
            ->where('area_uuid', Auth::user()->area_uuid)
            ->first();

        if (!$standard) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'room_temp_min' => $standard->room_temp_min,
            'room_temp_max' => $standard->room_temp_max,
            'setup_time_min' => $standard->setup_time_min,
            'setup_time_max' => $standard->setup_time_max,
            'core_temp_min' => $standard->core_temp_min,
            'core_temp_max' => $standard->core_temp_max,
        ]);
    }

    public function store(Request $request)
    {
        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        $validated = $request->validate([
            'date' => 'required|date',
            'product_uuid' => 'required|uuid',
            'product_code_range' => 'nullable|string',
            'gramase' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'curve_url' => 'nullable|url',
            'batches' => 'required|array|min:1',
            'batches.*.steamer_number' => 'nullable|string',
            'batches.*.trolley_count' => 'nullable|integer',
            'batches.*.tray_per_trolley' => 'nullable|integer',
            'batches.*.start_time' => 'nullable',
            'batches.*.end_time' => 'nullable',
            'batches.*.details' => 'required|array|min:1',
            'batches.*.details.*.production_code' => 'nullable|string',
            'batches.*.details.*.start_process' => 'nullable',
            'batches.*.details.*.end_process' => 'nullable',
            'batches.*.details.*.setup_time' => 'nullable|integer',
            'batches.*.details.*.room_temp' => 'nullable|numeric',
            'batches.*.details.*.sensory_bentuk' => 'nullable|string',
            'batches.*.details.*.sensory_warna' => 'nullable|string',
            'batches.*.details.*.sensory_aroma' => 'nullable|string',
            'batches.*.details.*.sensory_rasa' => 'nullable|string',
            'batches.*.details.*.sensory_tekstur' => 'nullable|string',
            'batches.*.details.*.core_temps' => 'nullable|array',
            'batches.*.details.*.core_temps.*' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $report = ReportSteamerCooking::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $validated['date'],
                'shift' => $shift,
                'product_uuid' => $validated['product_uuid'],
                'product_code_range' => $validated['product_code_range'] ?? null,
                'gramase' => $validated['gramase'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'curve_url' => $validated['curve_url'] ?? null,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($validated['batches'] as $batchData) {
                $batch = SteamerCookingBatch::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'steamer_number' => $batchData['steamer_number'],
                    'trolley_count' => $batchData['trolley_count'] ?? null,
                    'tray_per_trolley' => $batchData['tray_per_trolley'] ?? null,
                    'start_time' => $batchData['start_time'] ?? null,
                    'end_time' => $batchData['end_time'] ?? null,
                ]);

                foreach ($batchData['details'] as $detailData) {
                    $detail = SteamerCookingDetail::create([
                        'uuid' => Str::uuid(),
                        'batch_uuid' => $batch->uuid,
                        'production_code' => $detailData['production_code'] ?? null,
                        'start_process' => $detailData['start_process'] ?? null,
                        'end_process' => $detailData['end_process'] ?? null,
                        'setup_time' => $detailData['setup_time'] ?? null,
                        'room_temp' => $detailData['room_temp'] ?? null,
                        'sensory_bentuk' => $detailData['sensory_bentuk'] ?? null,
                        'sensory_warna' => $detailData['sensory_warna'] ?? null,
                        'sensory_aroma' => $detailData['sensory_aroma'] ?? null,
                        'sensory_rasa' => $detailData['sensory_rasa'] ?? null,
                        'sensory_tekstur' => $detailData['sensory_tekstur'] ?? null,
                    ]);

                    if (!empty($detailData['core_temps'])) {
                        foreach (array_values($detailData['core_temps']) as $i => $tempValue) {
                            if ($tempValue === null || $tempValue === '') {
                                continue;
                            }

                            SteamerCookingCoreTemp::create([
                                'uuid' => Str::uuid(),
                                'detail_uuid' => $detail->uuid,
                                'sequence' => $i + 1,
                                'temp_value' => $tempValue,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }

        return redirect()->route('report_steamer_cookings.index')
            ->with('success', 'Report berhasil disimpan.');
    }

    public function edit(ReportSteamerCooking $report_steamer_cooking)
    {
        $report_steamer_cooking->load('batches.details.coreTemps');
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('report_steamer_cookings.edit', [
            'report' => $report_steamer_cooking,
            'products' => $products,
        ]);
    }

    public function update(Request $request, ReportSteamerCooking $report_steamer_cooking)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'product_uuid' => 'required|uuid',
            'product_code_range' => 'nullable|string',
            'gramase' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'curve_url' => 'nullable|url',
            'batches' => 'required|array|min:1',
            'batches.*.uuid' => 'nullable|uuid',
            'batches.*.steamer_number' => 'nullable|string',
            'batches.*.trolley_count' => 'nullable|integer',
            'batches.*.tray_per_trolley' => 'nullable|integer',
            'batches.*.start_time' => 'nullable',
            'batches.*.end_time' => 'nullable',
            'batches.*.details' => 'required|array|min:1',
            'batches.*.details.*.uuid' => 'nullable|uuid',
            'batches.*.details.*.production_code' => 'nullable|string',
            'batches.*.details.*.start_process' => 'nullable',
            'batches.*.details.*.end_process' => 'nullable',
            'batches.*.details.*.setup_time' => 'nullable|integer',
            'batches.*.details.*.room_temp' => 'nullable|numeric',
            'batches.*.details.*.sensory_bentuk' => 'nullable|string',
            'batches.*.details.*.sensory_warna' => 'nullable|string',
            'batches.*.details.*.sensory_aroma' => 'nullable|string',
            'batches.*.details.*.sensory_rasa' => 'nullable|string',
            'batches.*.details.*.sensory_tekstur' => 'nullable|string',
            'batches.*.details.*.core_temps' => 'nullable|array',
            'batches.*.details.*.core_temps.*' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $report_steamer_cooking->update([
                'date' => $validated['date'],
                'product_uuid' => $validated['product_uuid'],
                'product_code_range' => $validated['product_code_range'] ?? null,
                'gramase' => $validated['gramase'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'curve_url' => $validated['curve_url'] ?? null,
            ]);

            // Strategi replace-all: hapus batch lama (cascade ke detail & core temp), buat ulang.
            // Konsisten dgn kebanyakan form kamu yg lain, kecuali kamu mau strategi UUID-matching seperti Startup Label.
            $report_steamer_cooking->batches()->delete();

            foreach ($validated['batches'] as $batchData) {
                $batch = SteamerCookingBatch::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report_steamer_cooking->uuid,
                    'steamer_number' => $batchData['steamer_number'],
                    'trolley_count' => $batchData['trolley_count'] ?? null,
                    'tray_per_trolley' => $batchData['tray_per_trolley'] ?? null,
                    'start_time' => $batchData['start_time'] ?? null,
                    'end_time' => $batchData['end_time'] ?? null,
                ]);

                foreach ($batchData['details'] as $detailData) {
                    $detail = SteamerCookingDetail::create([
                        'uuid' => Str::uuid(),
                        'batch_uuid' => $batch->uuid,
                        'production_code' => $detailData['production_code'] ?? null,
                        'start_process' => $detailData['start_process'] ?? null,
                        'end_process' => $detailData['end_process'] ?? null,
                        'setup_time' => $detailData['setup_time'] ?? null,
                        'room_temp' => $detailData['room_temp'] ?? null,
                        'sensory_bentuk' => $detailData['sensory_bentuk'] ?? null,
                        'sensory_warna' => $detailData['sensory_warna'] ?? null,
                        'sensory_aroma' => $detailData['sensory_aroma'] ?? null,
                        'sensory_rasa' => $detailData['sensory_rasa'] ?? null,
                        'sensory_tekstur' => $detailData['sensory_tekstur'] ?? null,
                    ]);

                    if (!empty($detailData['core_temps'])) {
                        foreach (array_values($detailData['core_temps']) as $i => $tempValue) {
                            if ($tempValue === null || $tempValue === '') {
                                continue;
                            }

                            SteamerCookingCoreTemp::create([
                                'uuid' => Str::uuid(),
                                'detail_uuid' => $detail->uuid,
                                'sequence' => $i + 1,
                                'temp_value' => $tempValue,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }

        return redirect()->route('report_steamer_cookings.index')
            ->with('success', 'Report berhasil diperbarui.');
    }

    public function destroy(ReportSteamerCooking $report_steamer_cooking)
    {
        $report_steamer_cooking->delete(); // cascade ke batches -> details -> core_temps

        return redirect()->route('report_steamer_cookings.index')
            ->with('success', 'Report berhasil dihapus.');
    }

public function exportPdf($uuid)
{
    $report = ReportSteamerCooking::with([
        'creator',
        'product',
        'area',
        'batches.details.coreTemps',
    ])->where('uuid', $uuid)->firstOrFail();

    $standard = SteamerStandard::where('product_uuid', $report->product_uuid)
        ->where('area_uuid', $report->area_uuid)
        ->first();

    // QR Diperiksa (created_by)
    $createdInfo = "Diperiksa oleh: " . ($report->creator->name ?? $report->created_by ?? '-') . "\nTanggal: " . $report->created_at->format('Y-m-d H:i');
    $createdQr = 'data:image/png;base64,' . base64_encode(QrCode::format('png')->size(150)->generate($createdInfo));

    // QR Diketahui (known_by)
    $knownInfo = $report->known_by
        ? "Diketahui oleh: {$report->known_by}"
        : "Belum diketahui";
    $knownQr = 'data:image/png;base64,' . base64_encode(QrCode::format('png')->size(150)->generate($knownInfo));

    // QR Disetujui (approved_by)
    $approvedInfo = $report->approved_by
        ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . optional($report->approved_at)->format('Y-m-d H:i')
        : "Belum disetujui";
    $approvedQr = 'data:image/png;base64,' . base64_encode(QrCode::format('png')->size(150)->generate($approvedInfo));

    $pdf = Pdf::loadView('report_steamer_cookings.export_pdf', [
        'report' => $report,
        'standard' => $standard,
        'createdQr' => $createdQr,
        'knownQr' => $knownQr,
        'approvedQr' => $approvedQr,
    ])->setPaper('a4', 'portrait');

    return $pdf->stream('report_steamer_cooking_' . $report->uuid . '.pdf');
}

    public function known($uuid)
    {
        $report = ReportSteamerCooking::where('uuid', $uuid)->firstOrFail();

        if ($report->known_by) {
            return back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = Auth::user()->name;
        $report->save();

        return back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve($uuid)
    {
        $report = ReportSteamerCooking::where('uuid', $uuid)->firstOrFail();

        if ($report->approved_by) {
            return back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = Auth::user()->name;
        $report->approved_at = now();
        $report->save();

        return back()->with('success', 'Laporan berhasil disetujui.');
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

        $reports = ReportSteamerCooking::with([
                'creator',
                'product',
                'batches.details.coreTemps',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'SteamerCooking_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(new SteamerCookingExport($reports, $periodLabel), $filename);
    }
}