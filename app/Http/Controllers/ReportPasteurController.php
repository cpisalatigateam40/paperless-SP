<?php

namespace App\Http\Controllers;

use App\Models\ReportPasteur;
use App\Models\DetailPasteur;
use App\Models\StepPasteur;
use App\Models\StandardStep;
use App\Models\DrainageStep;
use App\Models\FinishStep;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use App\Exports\PasteurExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use App\Http\Controllers\Traits\HasAuditController;
use App\Models\MasterPasteur;
use App\Models\MasterPasteurStep;
use App\Models\PasteurRun;


class ReportPasteurController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport, HasAuditController;

    protected function auditModel(): string { return ReportPasteur::class; }
    protected function auditRoutePrefix(): string { return 'report_pasteurs'; }
    protected function auditViewPrefix(): string { return 'report_pasteurs'; }
    protected function auditRelations(): array
    {
        return [
            'area',
            'details.product',
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
        ];
    }
    protected function auditSearchColumns(): array
    {
        return ['shift', 'problem', 'corrective_action', 'created_by'];
    }
    protected function auditDateColumn(): string { return 'date'; }
    protected function auditTimeColumn(): string { return 'created_at'; }
    protected function auditPlanColumn(): ?string { return null; }

    protected string $bulkModel = ReportPasteur::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportPasteur::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_pasteurs.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
            'area',
            'details.product',];
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
        return 'laporan_pasteurisasi';
    }

    public function index(Request $request)
    {
        $query = ReportPasteur::operasional()->with([
            'details.product',
            'details.runs.steps.standardStep',
            'details.runs.steps.finishStep',
            // jalur lama
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
            'area',
        ]);

        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // 🔍 GLOBAL SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('problem', 'like', "%{$search}%")
                ->orWhere('corrective_action', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL PASTEUR
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('program_number', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('for_packaging_gr', 'like', "%{$search}%")
                    ->orWhere('trolley_count', 'like', "%{$search}%")
                    ->orWhere('product_temp', 'like', "%{$search}%");
                });

                // 🔹 PRODUCT
                $q->orWhereHas('details.product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                });

                // 🔹 STEP PASTEUR (STANDARD / DRAINAGE / FINISH)
                $q->orWhereHas('details.steps', function ($s) use ($search) {
                    $s->where('step_name', 'like', "%{$search}%")
                    ->orWhere('step_type', 'like', "%{$search}%");
                });

                // 🔹 STANDARD STEP
                $q->orWhereHas('details.steps.standardStep', function ($st) use ($search) {
                    $st->where('water_temp', 'like', "%{$search}%")
                    ->orWhere('pressure', 'like', "%{$search}%")
                    ->orWhere('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%");
                });

                // 🔹 DRAINAGE STEP
                $q->orWhereHas('details.steps.drainageStep', function ($dr) use ($search) {
                    $dr->where('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%");
                });

                // 🔹 FINISH STEP
                $q->orWhereHas('details.steps.finishStep', function ($fs) use ($search) {
                    $fs->where('product_core_temp', 'like', "%{$search}%")
                    ->orWhere('sortation', 'like', "%{$search}%");
                });

                // 🔹 RUN (format baru)
                $q->orWhereHas('details.runs', function ($r) use ($search) {
                    $r->where('retort_chamber_number', 'like', "%{$search}%")
                    ->orWhere('trolley_count', 'like', "%{$search}%")
                    ->orWhere('tray_count', 'like', "%{$search}%")
                    ->orWhere('initial_temp', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
                });

                $q->orWhereHas('details.runs.steps.standardStep', function ($st) use ($search) {
                    $st->where('act_time', 'like', "%{$search}%");
                });
            });
        }

        // 📅 FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // 🔽 SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'relation' => 'details',
                'column' => 'product_code',
            ],
        ]);

        $reports = $query->paginate(10)->withQueryString();

        if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {

            $areas = Area::orderBy('name')->get();

        } else {

            $areas = collect();
        }

        return view('report_pasteurs.index', compact('reports', 'areas'));
    }

    /**
     * Form create report baru
     */
    public function create()
    {
        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_pasteurs.create', compact('areas', 'products'));
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
            $filePath = 'Pasteurisasi/' . $fileName;

            if (!Storage::disk('public')->exists('Pasteurisasi')) {
                Storage::disk('public')->makeDirectory('Pasteurisasi');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }

    /**
     * Simpan report baru
     */
    public function store(Request $request)
    {
        $request->validate($this->rules());

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        DB::transaction(function () use ($request, $shift) {
            // 1. Report
            $report = ReportPasteur::create([
                'area_uuid'         => Auth::user()->area_uuid,
                'date'              => $request->date,
                'shift'             => $shift,
                'created_by'        => Auth::user()->name,
                'problem'           => $request->problem,
                'corrective_action' => $request->corrective_action,
            ]);

            foreach ($request->input('details', []) as $d) {
                // 2. Detail (tanpa paraf, trolley_count, product_temp)
                $detail = DetailPasteur::create([
                    'report_uuid'      => $report->uuid,
                    'product_uuid'     => $d['product_uuid'],
                    'product_code'     => $d['product_code'] ?? null,
                    'program_number'   => $d['program_number'] ?? null,
                    'for_packaging_gr' => $d['for_packaging_gr'] ?? null,
                ]);

                // 3. Run (attempt 1 = utama, 2+ = pasteurisasi ulang)
                $master = $this->masterFor($d['product_uuid']);

                foreach (array_values($d['runs']) as $r => $runData) {
                    $this->saveRun($detail, $runData, $r + 1, $master);
                }
            }
        });

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Report Pasteurisasi berhasil ditambahkan');
    }

    private function saveRun(
        DetailPasteur $detail,
        array $data,
        int $attempt,
        ?MasterPasteur $master,
        ?PasteurRun $run = null,
        bool $refreshStd = false
    ): PasteurRun {
        $std      = $master?->steps->keyBy('step_order') ?? collect();
        $snapshot = !$run || $refreshStd;   // standar hanya diambil saat run baru / produk berganti

        $attrs = [
            'attempt'               => $attempt,
            'retort_chamber_number' => $data['retort_chamber_number'] ?? null,
            'trolley_count'         => $data['trolley_count'] ?? null,
            'tray_count'            => $data['tray_count'] ?? null,
            'start_time'            => $data['start_time'] ?? null,
            'end_time'              => $data['end_time'] ?? null,
            'initial_temp'          => $data['initial_temp'] ?? null,
            'sensor_ok'             => (isset($data['sensor_ok']) && $data['sensor_ok'] !== '')
                ? (bool) $data['sensor_ok'] : null,
            'notes'                 => $data['notes'] ?? null,
        ];

        if ($snapshot) {
            $attrs['initial_temp_min'] = $master?->initial_temp_min;
            $attrs['initial_temp_max'] = $master?->initial_temp_max;
        }

        if ($run) {
            $run->update($attrs);
        } else {
            $run = PasteurRun::create($attrs + ['detail_uuid' => $detail->uuid]);
        }

        // Step 1-8
        foreach (MasterPasteurStep::STEPS as $order => $name) {
            $row = $data['steps'][$order] ?? [];

            $step = StepPasteur::firstOrCreate(
                ['run_uuid' => $run->uuid, 'step_order' => $order],
                ['detail_uuid' => $detail->uuid, 'step_name' => $name, 'step_type' => 'standard']
            );

            $vals = [
                'act_time'   => $row['act_time'] ?? null,
                'water_temp' => $row['water_temp'] ?? null,
                'pressure'   => $row['pressure'] ?? null,
            ];

            if ($snapshot) {
                $s = $std[$order] ?? null;
                $vals += [
                    'set_time'           => $s?->set_time,
                    'set_water_temp_min' => $s?->water_temp_min,
                    'set_water_temp_max' => $s?->water_temp_max,
                    'set_pressure_min'   => $s?->pressure_min,
                    'set_pressure_max'   => $s?->pressure_max,
                ];
            }

            StandardStep::updateOrCreate(['step_uuid' => $step->uuid], $vals);
        }

        // Finish
        $finish = StepPasteur::firstOrCreate(
            ['run_uuid' => $run->uuid, 'step_order' => count(MasterPasteurStep::STEPS) + 1],
            ['detail_uuid' => $detail->uuid, 'step_name' => 'Finish', 'step_type' => 'finish']
        );

        FinishStep::updateOrCreate(
            ['step_uuid' => $finish->uuid],
            ['product_core_temp' => $data['product_core_temp'] ?? null]
        );

        return $run;
    }

    private array $masterCache = [];

    private function masterFor(?string $productUuid): ?MasterPasteur
    {
        if (!$productUuid) return null;

        return $this->masterCache[$productUuid]
            ??= MasterPasteur::with('steps')->where('product_uuid', $productUuid)->first();
    }

    private function deleteRun(PasteurRun $run): void
    {
        $stepUuids = StepPasteur::where('run_uuid', $run->uuid)->pluck('uuid');

        StandardStep::whereIn('step_uuid', $stepUuids)->delete();
        FinishStep::whereIn('step_uuid', $stepUuids)->delete();
        StepPasteur::where('run_uuid', $run->uuid)->delete();
        $run->delete();
    }

    private function rules(bool $requireDetails = true): array
    {
        return [
            'date'                                      => 'required|date',
            'details'                                   => $requireDetails ? 'required|array|min:1' : 'nullable|array',
            'details.*.uuid'                            => 'nullable|string',
            'details.*.product_uuid'                    => 'required',
            'details.*.runs'                            => 'required|array|min:1',
            'details.*.runs.*.uuid'                     => 'nullable|string',
            'details.*.runs.*.retort_chamber_number'    => 'nullable|string|max:20',
            'details.*.runs.*.trolley_count'            => 'nullable|integer|min:0',
            'details.*.runs.*.tray_count'               => 'nullable|integer|min:0',
            'details.*.runs.*.start_time'               => 'nullable|date_format:H:i',
            'details.*.runs.*.end_time'                 => 'nullable|date_format:H:i',
            'details.*.runs.*.initial_temp'             => 'nullable|numeric',
            'details.*.runs.*.sensor_ok'                => 'nullable|in:0,1',
            'details.*.runs.*.product_core_temp'        => 'nullable|numeric',
            'details.*.runs.*.steps.*.act_time'         => 'nullable|numeric|min:0',
            'details.*.runs.*.steps.*.water_temp'       => 'nullable|numeric',
            'details.*.runs.*.steps.*.pressure'         => 'nullable|numeric|min:0',
        ];
    }



    /**
     * Hapus report
     */
    public function destroy($uuid)
    {
        $report = ReportPasteur::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Report berhasil dihapus');
    }

    public function addDetail($reportUuid)
    {
        $report = ReportPasteur::where('uuid', $reportUuid)->firstOrFail();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_pasteurs.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportPasteur::where('uuid', $reportUuid)->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $index => $detailData) {

                $qcParafPath = null;
                if (!empty($detailData['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detailData['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detailData['production_paraf'])) {
                    $productionParafPath = $this->saveSignature($detailData['production_paraf'], "production_{$index}");
                }

                $detail = DetailPasteur::create([
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'] ?? null,
                    'program_number' => $detailData['program_number'] ?? null,
                    'product_code' => $detailData['product_code'] ?? null,
                    'for_packaging_gr' => $detailData['for_packaging_gr'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'product_temp' => $detailData['product_temp'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'production_paraf' => $productionParafPath,
                ]);

                // simpan step
                if (isset($detailData['steps'])) {
                    foreach ($detailData['steps'] as $stepData) {
                        $step = StepPasteur::create([
                            'detail_uuid' => $detail->uuid,
                            'step_name' => $stepData['step_name'] ?? null,
                            'step_order' => $stepData['step_order'] ?? null,
                            'step_type' => $stepData['step_type'] ?? null,
                        ]);

                        if (($stepData['step_type'] ?? '') === 'standard') {
                            StandardStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'drainage') {
                            DrainageStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'finish') {
                            FinishStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }
                    }
                }
            }
        }

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Detail berhasil ditambahkan ke laporan pasteurisasi');
    }

    public function known($id)
    {
        $report = ReportPasteur::findOrFail($id);
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
        $report = ReportPasteur::findOrFail($id);
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
        // Ambil report beserta relasi
        $report = ReportPasteur::with([
            'details.product',
            'details.runs.steps.standardStep',
            'details.runs.steps.finishStep',
            // format lama
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
            'area',
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

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_pasteurs');


        // Kirim data ke view PDF
        $pdf = Pdf::loadView('report_pasteurs.pdf', [
            'report'     => $report,
            'createdQr'  => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr'    => $knownQrBase64,
            'formNumber' => $formNumber,
        ])->setPaper('a4', 'portrait');

        // Download PDF
        return $pdf->stream("Report_Pasteur_{$report->uuid}.pdf");
    }

    public function edit($uuid)
    {
        $report = ReportPasteur::with([
            'details.product',
            'details.runs.steps.standardStep',
            'details.runs.steps.finishStep',
            // untuk deteksi & tampilan format lama
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
        ])->where('uuid', $uuid)->firstOrFail();

        $areas    = Area::all();
        $products = Product::uniqueByName();

        return view('report_pasteurs.edit', compact('report', 'areas', 'products'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportPasteur::with(['details.runs', 'details.steps'])
            ->where('uuid', $uuid)->firstOrFail();

        // Laporan yang isinya hanya detail lama tidak punya input detail di form
        $hasEditable = $report->details->contains(fn ($d) => !$d->isLegacy());
        $request->validate($this->rules($hasEditable));

        DB::transaction(function () use ($request, $report) {
            $report->update([
                'date'              => $request->date,
                'shift'             => $request->shift,
                'problem'           => $request->problem,
                'corrective_action' => $request->corrective_action,
            ]);

            foreach ($request->input('details', []) as $d) {
                // Hanya detail milik laporan ini, dan bukan detail lama
                $detail = $report->details->firstWhere('uuid', $d['uuid'] ?? null);
                if (!$detail || $detail->isLegacy()) continue;

                $productChanged = $detail->product_uuid !== ($d['product_uuid'] ?? null);

                // Paraf, trolley_count, product_temp sengaja tidak disentuh
                $detail->update([
                    'product_uuid'     => $d['product_uuid'],
                    'product_code'     => $d['product_code'] ?? null,
                    'program_number'   => $d['program_number'] ?? null,
                    'for_packaging_gr' => $d['for_packaging_gr'] ?? null,
                ]);

                $master   = $this->masterFor($d['product_uuid']);
                $runsIn   = array_values($d['runs'] ?? []);
                $keepRuns = collect($runsIn)->pluck('uuid')->filter()->all();

                // Hapus run yang dibuang user, SEBELUM renumber attempt (unique detail_uuid+attempt)
                $detail->runs
                    ->whereNotIn('uuid', $keepRuns)
                    ->each(fn ($r) => $this->deleteRun($r));

                foreach ($runsIn as $i => $runData) {
                    $run = $detail->runs->firstWhere('uuid', $runData['uuid'] ?? null);
                    $this->saveRun($detail, $runData, $i + 1, $master, $run, $productChanged);
                }
            }
        });

        return redirect()
            ->route($report->is_audit ? 'report_pasteurs.audit' : 'report_pasteurs.index')
            ->with('success', $report->is_audit
                ? 'Data audit berhasil diperbarui.'
                : 'Data berhasil diperbarui.');
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
    
        $reports = ReportPasteur::operasional()
            ->with([
                'details.product',
                'details.steps.standardStep',
                'details.steps.drainageStep',
                'details.steps.finishStep',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Pasteurisasi_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new PasteurExport($reports, $periodLabel), $filename);
    }

    public function masterByProduct(string $productUuid)
    {
        $gramase = Product::where('uuid', $productUuid)->value('nett_weight');

        $master = MasterPasteur::with('steps')->where('product_uuid', $productUuid)->first();

        if (!$master) {
            return response()->json(['found' => false, 'gramase' => $gramase]);
        }

        return response()->json([
            'found'            => true,
            'gramase'          => $gramase,
            'initial_temp_min' => $master->initial_temp_min,
            'initial_temp_max' => $master->initial_temp_max,
            'steps'            => $master->steps->keyBy('step_order')->map(fn ($s) => [
                'set_time'       => $s->set_time,
                'water_temp_min' => $s->water_temp_min,
                'water_temp_max' => $s->water_temp_max,
                'pressure_min'   => $s->pressure_min,
                'pressure_max'   => $s->pressure_max,
            ]),
        ]);
    }
}