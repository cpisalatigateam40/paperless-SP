<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\Section;
use App\Models\ReportSmokeHouse;
use App\Models\DetailSmokeHouse;
use App\Models\DetailSmokeHouseStep;
use App\Models\DetailSmokeHouseRework;
use App\Models\DetailSmokeHouseReworkStep;
use App\Models\DetailSmokeHouseSensory;
use App\Models\MasterSmokeHouse;
use App\Models\MasterSmokeHouseStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\Facades\DNS2DFacade; // sesuaikan kalau pakai simplesoftwareio/simple-qrcode
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SmokeHouseExport;

class ReportSmokeHouseController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport;
    protected string $bulkModel = ReportSmokeHouse::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportSmokeHouse::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report-smoke-houses.export_pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['creator',
            'details.product',
            'details.steps',
            'details.reworks.steps',
            'details.sensories',];
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
        return 'laporan_smoke_house';
    }
    
    public function index()
    {
        $reports = ReportSmokeHouse::with([
            'area',
            'creator',
            'details' => function ($q) {
                $q->with([
                    'product',
                    'steps',
                    'reworks.steps',
                    'sensories',
                ]);
            }
        ])
        ->latest()
        ->paginate(10);

        return view('report-smoke-houses.index', compact('reports'));
    }

    public function create()
    {
        $sections = Section::orderBy('section_name')->get();

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('report-smoke-houses.create', compact(
            'sections',
            'products'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'notes' => 'nullable|string',

            'details' => 'required|array|min:1',
            'details.*.master_uuid' => 'nullable|exists:master_smoke_houses,uuid',
            'details.*.product_uuid' => 'required|exists:products,uuid',
            'details.*.machine_name' => 'required|string',
            'details.*.production_code' => 'required|string',
            'details.*.gramase' => 'nullable|numeric',
            'details.*.smoke_house_no' => 'nullable|integer',
            'details.*.trolley_count' => 'nullable|integer',
            'details.*.stick_count' => 'nullable|integer',
            'details.*.start_process' => 'nullable|date',
            'details.*.end_process' => 'nullable|date',
            'details.*.cooling_finish' => 'nullable|date',

            'details.*.steps' => 'nullable|array',
            'details.*.steps.*.sequence' => 'nullable|integer',
            'details.*.steps.*.process_name' => 'nullable|string',
            'details.*.steps.*.setting_temp' => 'nullable|string',
            'details.*.steps.*.setting_time' => 'nullable',
            'details.*.steps.*.setting_rh' => 'nullable',
            'details.*.steps.*.setting_ct' => 'nullable',
            'details.*.steps.*.actual_temp' => 'nullable',
            'details.*.steps.*.actual_time' => 'nullable',
            'details.*.steps.*.actual_rh' => 'nullable',
            'details.*.steps.*.actual_ct' => 'nullable',

            'details.*.reworks' => 'nullable|array',
            'details.*.reworks.*.smoke_house_no' => 'nullable|integer',
            'details.*.reworks.*.trolley_count' => 'nullable|integer',
            'details.*.reworks.*.stick_count' => 'nullable|integer',
            'details.*.reworks.*.start_process' => 'nullable|date',
            'details.*.reworks.*.end_process' => 'nullable|date',
            'details.*.reworks.*.steps' => 'nullable|array',
            'details.*.reworks.*.steps.*.process_name' => 'nullable|string',
            'details.*.reworks.*.steps.*.setting_temp' => 'nullable',
            'details.*.reworks.*.steps.*.setting_time' => 'nullable',
            'details.*.reworks.*.steps.*.setting_rh' => 'nullable',
            'details.*.reworks.*.steps.*.setting_ct' => 'nullable',
            'details.*.reworks.*.steps.*.actual_temp' => 'nullable',
            'details.*.reworks.*.steps.*.actual_time' => 'nullable',
            'details.*.reworks.*.steps.*.actual_rh' => 'nullable',
            'details.*.reworks.*.steps.*.actual_ct' => 'nullable',

            'details.*.sensories.appearance' => 'nullable|string',
            'details.*.sensories.color' => 'nullable|string',
            'details.*.sensories.aroma' => 'nullable|string',
            'details.*.sensories.taste' => 'nullable|string',
            'details.*.sensories.texture' => 'nullable|string',
            'details.*.sensories.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {

        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

            $report = ReportSmokeHouse::create([
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => null,
                'date' => $validated['date'],
                'shift' => $shift,
                'created_by' => Auth::user()->uuid,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['details'] as $detailData) {

                $detail = DetailSmokeHouse::create([
                    'report_uuid' => $report->uuid,
                    'master_uuid' => $detailData['master_uuid'] ?: null,
                    'product_uuid' => $detailData['product_uuid'],
                    'machine_name' => $detailData['machine_name'],
                    'production_code' => $detailData['production_code'],
                    'gramase' => $detailData['gramase'] ?? null,
                    'smoke_house_no' => $detailData['smoke_house_no'],
                    'trolley_count' => $detailData['trolley_count'],
                    'stick_count' => $detailData['stick_count'],
                    'start_process' => $detailData['start_process'] ?? null,
                    'end_process' => $detailData['end_process'] ?? null,
                    'cooling_finish' => $detailData['cooling_finish'] ?? null,
                ]);

                // ===== Steps (cooking + showering, satu tabel yang sama) =====
                foreach ($detailData['steps'] ?? [] as $step) {

                    if (empty($step['process_name'])) {
                        continue;
                    }

                    DetailSmokeHouseStep::create([
                        'detail_uuid' => $detail->uuid,
                        'sequence' => $step['sequence'] ?? 1,
                        'process_name' => $step['process_name'],
                        'setting_temp' => $step['setting_temp'] ?? null,
                        'setting_time' => $step['setting_time'] ?? null,
                        'setting_rh' => $step['setting_rh'] ?? null,
                        'setting_ct' => $step['setting_ct'] ?? null,
                        'actual_temp' => $step['actual_temp'] ?? null,
                        'actual_time' => $step['actual_time'] ?? null,
                        'actual_rh' => $step['actual_rh'] ?? null,
                        'actual_ct' => $step['actual_ct'] ?? null,
                    ]);
                }

                // ===== Cooking Ulang (rework) =====
                foreach ($detailData['reworks'] ?? [] as $reworkData) {

                    $rework = DetailSmokeHouseRework::create([
                        'detail_uuid' => $detail->uuid,
                        'smoke_house_no' => $reworkData['smoke_house_no'] ?? null,
                        'trolley_count' => $reworkData['trolley_count'] ?? null,
                        'stick_count' => $reworkData['stick_count'] ?? null,
                        'start_process' => $reworkData['start_process'] ?? null,
                        'end_process' => $reworkData['end_process'] ?? null,
                    ]);

                    foreach (($reworkData['steps'] ?? []) as $rIndex => $rStep) {

                        if (empty($rStep['process_name'])) {
                            continue;
                        }

                        DetailSmokeHouseReworkStep::create([
                            'rework_uuid' => $rework->uuid,
                            'sequence' => $rIndex + 1,
                            'process_name' => $rStep['process_name'],
                            'setting_temp' => $rStep['setting_temp'] ?? null,
                            'setting_time' => $rStep['setting_time'] ?? null,
                            'setting_rh' => $rStep['setting_rh'] ?? null,
                            'setting_ct' => $rStep['setting_ct'] ?? null,
                            'actual_temp' => $rStep['actual_temp'] ?? null,
                            'actual_time' => $rStep['actual_time'] ?? null,
                            'actual_rh' => $rStep['actual_rh'] ?? null,
                            'actual_ct' => $rStep['actual_ct'] ?? null,
                        ]);
                    }
                }

                // ===== Sensori (hanya simpan kalau ada isinya) =====
                $sensoryData = $detailData['sensories'] ?? [];

                if (array_filter($sensoryData)) {
                    DetailSmokeHouseSensory::create([
                        'detail_uuid' => $detail->uuid,
                        'appearance' => $sensoryData['appearance'] ?? null,
                        'color' => $sensoryData['color'] ?? null,
                        'aroma' => $sensoryData['aroma'] ?? null,
                        'taste' => $sensoryData['taste'] ?? null,
                        'texture' => $sensoryData['texture'] ?? null,
                        'notes' => $sensoryData['notes'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('report-smoke-houses.index')
            ->with('success', 'Report Smoke House berhasil disimpan.');
    }

    public function edit($uuid)
    {
        $report = ReportSmokeHouse::with([
            'details.product',
            'details.steps',
            'details.reworks.steps',
            'details.sensories',
        ])->firstWhere('uuid', $uuid);

        abort_if(!$report, 404);

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('report-smoke-houses.edit', compact(
            'report',
            'products'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'notes' => 'nullable|string',

            'details' => 'required|array|min:1',
            'details.*.master_uuid' => 'nullable|exists:master_smoke_houses,uuid',
            'details.*.product_uuid' => 'required|exists:products,uuid',
            'details.*.machine_name' => 'required|string',
            'details.*.production_code' => 'required|string',
            'details.*.gramase' => 'nullable|numeric',
            'details.*.smoke_house_no' => 'nullable|integer',
            'details.*.trolley_count' => 'nullable|integer',
            'details.*.stick_count' => 'nullable|integer',
            'details.*.start_process' => 'nullable|date',
            'details.*.end_process' => 'nullable|date',
            'details.*.cooling_finish' => 'nullable|date',

            'details.*.steps' => 'nullable|array',
            'details.*.steps.*.sequence' => 'nullable|integer',
            'details.*.steps.*.process_name' => 'nullable|string',
            'details.*.steps.*.setting_temp' => 'nullable|string',
            'details.*.steps.*.setting_time' => 'nullable',
            'details.*.steps.*.setting_rh' => 'nullable',
            'details.*.steps.*.setting_ct' => 'nullable',
            'details.*.steps.*.actual_temp' => 'nullable',
            'details.*.steps.*.actual_time' => 'nullable',
            'details.*.steps.*.actual_rh' => 'nullable',
            'details.*.steps.*.actual_ct' => 'nullable',

            'details.*.reworks' => 'nullable|array',
            'details.*.reworks.*.smoke_house_no' => 'nullable|integer',
            'details.*.reworks.*.trolley_count' => 'nullable|integer',
            'details.*.reworks.*.stick_count' => 'nullable|integer',
            'details.*.reworks.*.start_process' => 'nullable|date',
            'details.*.reworks.*.end_process' => 'nullable|date',
            'details.*.reworks.*.steps' => 'nullable|array',
            'details.*.reworks.*.steps.*.process_name' => 'nullable|string',
            'details.*.reworks.*.steps.*.setting_temp' => 'nullable',
            'details.*.reworks.*.steps.*.setting_time' => 'nullable',
            'details.*.reworks.*.steps.*.setting_rh' => 'nullable',
            'details.*.reworks.*.steps.*.setting_ct' => 'nullable',
            'details.*.reworks.*.steps.*.actual_temp' => 'nullable',
            'details.*.reworks.*.steps.*.actual_time' => 'nullable',
            'details.*.reworks.*.steps.*.actual_rh' => 'nullable',
            'details.*.reworks.*.steps.*.actual_ct' => 'nullable',

            'details.*.sensories.appearance' => 'nullable|string',
            'details.*.sensories.color' => 'nullable|string',
            'details.*.sensories.aroma' => 'nullable|string',
            'details.*.sensories.taste' => 'nullable|string',
            'details.*.sensories.texture' => 'nullable|string',
            'details.*.sensories.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $uuid) {

            $report = ReportSmokeHouse::with('details.steps', 'details.reworks.steps', 'details.sensories')
                ->firstWhere('uuid', $uuid);

            abort_if(!$report, 404);

            $shift = auth()->user()->hasRole('QC Inspector')
                ? session('shift_number') . '-' . session('shift_group')
                : ($validated['shift'] ?? 'NON-SHIFT');

            $report->update([
                'date' => $validated['date'],
                'shift' => $shift,
                'notes' => $validated['notes'] ?? null,
            ]);

            // ===== Hapus semua detail lama beserta seluruh child-nya =====
            foreach ($report->details as $oldDetail) {

                $oldDetail->steps()->delete();

                foreach ($oldDetail->reworks as $oldRework) {
                    $oldRework->steps()->delete();
                    $oldRework->delete();
                }

                if ($oldDetail->sensories) {
                    $oldDetail->sensories()->delete();
                }

                $oldDetail->delete();
            }

            // ===== Recreate semua detail baru (persis logic store()) =====
            foreach ($validated['details'] as $detailData) {

                $detail = DetailSmokeHouse::create([
                    'report_uuid' => $report->uuid,
                    'master_uuid' => $detailData['master_uuid'] ?: null,
                    'product_uuid' => $detailData['product_uuid'],
                    'machine_name' => $detailData['machine_name'],
                    'production_code' => $detailData['production_code'],
                    'gramase' => $detailData['gramase'] ?? null,
                    'smoke_house_no' => $detailData['smoke_house_no'],
                    'trolley_count' => $detailData['trolley_count'],
                    'stick_count' => $detailData['stick_count'],
                    'start_process' => $detailData['start_process'] ?? null,
                    'end_process' => $detailData['end_process'] ?? null,
                    'cooling_finish' => $detailData['cooling_finish'] ?? null,
                ]);

                foreach ($detailData['steps'] ?? [] as $step) {

                    if (empty($step['process_name'])) {
                        continue;
                    }

                    DetailSmokeHouseStep::create([
                        'detail_uuid' => $detail->uuid,
                        'sequence' => $step['sequence'] ?? 1,
                        'process_name' => $step['process_name'],
                        'setting_temp' => $step['setting_temp'] ?? null,
                        'setting_time' => $step['setting_time'] ?? null,
                        'setting_rh' => $step['setting_rh'] ?? null,
                        'setting_ct' => $step['setting_ct'] ?? null,
                        'actual_temp' => $step['actual_temp'] ?? null,
                        'actual_time' => $step['actual_time'] ?? null,
                        'actual_rh' => $step['actual_rh'] ?? null,
                        'actual_ct' => $step['actual_ct'] ?? null,
                    ]);
                }

                foreach ($detailData['reworks'] ?? [] as $reworkData) {

                    $rework = DetailSmokeHouseRework::create([
                        'detail_uuid' => $detail->uuid,
                        'smoke_house_no' => $reworkData['smoke_house_no'] ?? null,
                        'trolley_count' => $reworkData['trolley_count'] ?? null,
                        'stick_count' => $reworkData['stick_count'] ?? null,
                        'start_process' => $reworkData['start_process'] ?? null,
                        'end_process' => $reworkData['end_process'] ?? null,
                    ]);

                    foreach (($reworkData['steps'] ?? []) as $rIndex => $rStep) {

                        if (empty($rStep['process_name'])) {
                            continue;
                        }

                        DetailSmokeHouseReworkStep::create([
                            'rework_uuid' => $rework->uuid,
                            'sequence' => $rIndex + 1,
                            'process_name' => $rStep['process_name'],
                            'setting_temp' => $rStep['setting_temp'] ?? null,
                            'setting_time' => $rStep['setting_time'] ?? null,
                            'setting_rh' => $rStep['setting_rh'] ?? null,
                            'setting_ct' => $rStep['setting_ct'] ?? null,
                            'actual_temp' => $rStep['actual_temp'] ?? null,
                            'actual_time' => $rStep['actual_time'] ?? null,
                            'actual_rh' => $rStep['actual_rh'] ?? null,
                            'actual_ct' => $rStep['actual_ct'] ?? null,
                        ]);
                    }
                }

                $sensoryData = $detailData['sensories'] ?? [];

                if (array_filter($sensoryData)) {
                    DetailSmokeHouseSensory::create([
                        'detail_uuid' => $detail->uuid,
                        'appearance' => $sensoryData['appearance'] ?? null,
                        'color' => $sensoryData['color'] ?? null,
                        'aroma' => $sensoryData['aroma'] ?? null,
                        'taste' => $sensoryData['taste'] ?? null,
                        'texture' => $sensoryData['texture'] ?? null,
                        'notes' => $sensoryData['notes'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('report-smoke-houses.index')
            ->with('success', 'Report Smoke House berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $report = ReportSmokeHouse::firstWhere('uuid', $uuid);

        abort_if(!$report, 404);

        $report->delete();

        return redirect()
            ->route('report-smoke-houses.index')
            ->with('success', 'Report Smoke House berhasil dihapus.');
    }

    public function getMachines($product_uuid)
    {
        $masters = MasterSmokeHouse::where('product_uuid', $product_uuid)
            ->orderBy('machine_name')
            ->get(['uuid', 'machine_name']);

        return response()->json($masters);
    }

    public function getMasterSteps($master_uuid)
    {
        $master = MasterSmokeHouse::with('steps')->firstWhere('uuid', $master_uuid);
        abort_if(!$master, 404);

        return response()->json($master->steps);
    }

    // dipakai nanti untuk autofill 1 step di Cooking Ulang, tanpa fetch semua steps
    public function getMasterStep($master_uuid, $process_name)
    {
        $step = MasterSmokeHouseStep::where('master_uuid', $master_uuid)
            ->where('process_name', $process_name)
            ->first();

        abort_if(!$step, 404);

        return response()->json($step);
    }

    public function exportPdf($uuid)
    {
        $report = ReportSmokeHouse::with([
            'creator',
            'details.product',
            'details.steps',
            'details.reworks.steps',
            'details.sensories',
        ])->where('uuid', $uuid)->firstOrFail();

        // QR Diperiksa (created_by)
        $createdInfo = "Diperiksa oleh: " . ($report->creator->name ?? '-') . "\nTanggal: " . $report->created_at->format('Y-m-d H:i');
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

        $pdf = Pdf::loadView('report-smoke-houses.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQr,
            'knownQr' => $knownQr,
            'approvedQr' => $approvedQr,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('report_smoke_house_' . $report->uuid . '.pdf');
    }

    public function known($id)
    {
        $report = ReportSmokeHouse::findOrFail($id);
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
        $report = ReportSmokeHouse::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
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

        $reports = ReportSmokeHouse::with([
                'creator',
                'details.product',
                'details.steps',
                'details.reworks.steps',
                'details.sensories',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'SmokeHouse_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(new SmokeHouseExport($reports, $periodLabel), $filename);
    }
}