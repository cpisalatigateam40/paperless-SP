<?php

namespace App\Http\Controllers;

use App\Models\ReportWaterbath;
use App\Models\DetailWaterbath;
use App\Models\PasteurisasiWaterbath;
use App\Models\CoolingShockWaterbath;
use App\Models\DrippingWaterbath;
use App\Models\MasterWaterbath;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\WaterbathExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use App\Http\Controllers\Traits\HasAuditController;
use App\Models\BatchWaterbath;
use App\Models\MasterWaterbathParameter;
use App\Models\RunParameterWaterbath;
use App\Models\RunWaterbath;
use App\Exports\WaterbathWorkbookExport;
use App\Models\RunStepWaterbath;

class ReportWaterbathController extends Controller
{
    use HasBulkApproval,HasBulkPdfExport, HasSortableReport, HasAuditController;

    protected function auditModel(): string { return ReportWaterbath::class; }
    protected function auditRoutePrefix(): string { return 'report_waterbaths'; }
    protected function auditViewPrefix(): string { return 'report_waterbaths'; }
    protected function auditRelations(): array
    {
        return [
            'area', 'details.product', 'pasteurisasi', 'coolingShocks', 'drippings',
            'product', 'batches', 'runs.parameters', 'runs.steps',
        ];
    }
    protected function auditSearchColumns(): array { return ['shift', 'created_by']; }
    protected function auditDateColumn(): string { return 'date'; }
    protected function auditTimeColumn(): string { return 'created_at'; }
    protected function auditPlanColumn(): ?string { return null; }

    protected string $bulkModel = ReportWaterbath::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportWaterbath::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_waterbaths.export_pdf_bulk_item';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return [
            'area', 'details.product', 'pasteurisasi', 'coolingShocks', 'drippings',
            // form v2
            'product', 'batches', 'runs.parameters', 'runs.steps',
        ];
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
            'formNumber' => \App\Models\FormNumber::get($report->area->uuid, 'report_waterbaths'),
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_waterbath';
    }

    public function index(Request $request)
    {
        $search = $request->search;

        $query = ReportWaterbath::operasional()->with([
            'area',
            'details.product',
            'pasteurisasi',
            'coolingShocks',
            'drippings',
            // form v2
            'product',
            'batches',
            'runs.parameters',
            'runs.steps',
        ]);
        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {

                /* ================= HEADER ================= */
                $qq->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                /* ================= AREA ================= */
                $qq->orWhereHas('area', function ($qa) use ($search) {
                    $qa->where('name', 'like', "%{$search}%");
                });

                /* ================= DETAIL ================= */
                $qq->orWhereHas('details', function ($qd) use ($search) {
                    $qd->where('batch_code', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($qp) use ($search) {
                            $qp->where('product_name', 'like', "%{$search}%");
                        });
                });

                /* ================= PASTEURISASI ================= */
                $qq->orWhereHas('pasteurisasi', function ($qp) use ($search) {
                    $qp->where('initial_product_temp', 'like', "%{$search}%")
                        ->orWhere('initial_water_temp', 'like', "%{$search}%")
                        ->orWhere('water_temp_setting', 'like', "%{$search}%")
                        ->orWhere('water_temp_actual', 'like', "%{$search}%")
                        ->orWhere('water_temp_final', 'like', "%{$search}%")
                        ->orWhere('product_temp_final', 'like', "%{$search}%")
                        ->orWhere('start_time_pasteur', 'like', "%{$search}%")
                        ->orWhere('stop_time_pasteur', 'like', "%{$search}%");
                });

                /* ================= COOLING SHOCK ================= */
                $qq->orWhereHas('coolingShocks', function ($qc) use ($search) {
                    $qc->where('initial_water_temp', 'like', "%{$search}%")
                        ->orWhere('water_temp_setting', 'like', "%{$search}%")
                        ->orWhere('water_temp_actual', 'like', "%{$search}%")
                        ->orWhere('water_temp_final', 'like', "%{$search}%")
                        ->orWhere('product_temp_final', 'like', "%{$search}%")
                        ->orWhere('start_time_pasteur', 'like', "%{$search}%")
                        ->orWhere('stop_time_pasteur', 'like', "%{$search}%");
                });

                /* ================= DRIPPING ================= */
                $qq->orWhereHas('drippings', function ($qd) use ($search) {
                    $qd->where('hot_zone_temperature', 'like', "%{$search}%")
                        ->orWhere('cold_zone_temperature', 'like', "%{$search}%")
                        ->orWhere('product_temp_final', 'like', "%{$search}%")
                        ->orWhere('start_time_pasteur', 'like', "%{$search}%")
                        ->orWhere('stop_time_pasteur', 'like', "%{$search}%");
                });

                /* ================= FORM V2 ================= */
                $qq->orWhereHas('product', function ($qp) use ($search) {
                    $qp->where('product_name', 'like', "%{$search}%");
                });

                $qq->orWhereHas('batches', function ($qb) use ($search) {
                    $qb->where('batch_code', 'like', "%{$search}%");
                });

                $qq->orWhereHas('runs', function ($qr) use ($search) {
                    $qr->where('waterbath_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

            });
        });

        // 📅 FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // 🔽 SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'relation' => 'details',
                'column' => 'batch_code',
            ],
        ]);

        $reports = $query->paginate(10)
            ->withQueryString();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_waterbaths.index', compact('reports', 'areas'));
    }

    // public function create()
    // {
    //     $products = Product::uniqueByName();

    //     // Sementara: Cikande 1 masih form lama sampai formatnya dibuat
    //     if ($this->usesLegacyForm()) {
    //         return view('report_waterbaths.create', compact('products'));
    //     }

    //     return view('report_waterbaths.v2.create', compact('products'));
    // }

    // public function store(Request $request)
    // {
    //     if ($this->usesLegacyForm()) {
    //         return $this->storeLegacy($request);
    //     }

    //     $isQc = auth()->user()->hasRole('QC Inspector');

    //     $data = $request->validate($this->rulesV2());

    //     $shift = $isQc
    //         ? session('shift_number') . '-' . session('shift_group')
    //         : ($data['shift'] ?? 'NON-SHIFT');

    //     DB::transaction(function () use ($data, $shift) {
    //         $report = ReportWaterbath::create([
    //             'uuid'         => (string) Str::uuid(),
    //             'area_uuid'    => Auth::user()->area_uuid,
    //             'date'         => $data['date'],
    //             'shift'        => $shift,
    //             'form_version' => 2,
    //             'product_uuid' => $data['product_uuid'],
    //             'gramase'      => $this->num($data['gramase'] ?? null),
    //             'created_by'   => Auth::user()->name,
    //         ]);

    //         if (filled($data['batch_code'] ?? null)) {
    //             BatchWaterbath::create([
    //                 'uuid'        => (string) Str::uuid(),
    //                 'report_uuid' => $report->uuid,
    //                 'batch_code'  => $data['batch_code'],
    //             ]);
    //         }

    //         // Standar diambil dari master di server (bukan dari hidden input form)
    //         $master    = MasterWaterbath::findForProduct($data['product_uuid']);
    //         $standards = $master ? $master->parameters->keyBy('parameter') : collect();

    //         $runNumber = 0;

    //         foreach (array_values($data['runs']) as $idx => $r) {
    //             // Blok pasteurisasi ulang yang dibiarkan kosong tidak disimpan
    //             if ($idx > 0 && $this->runIsEmpty($r)) {
    //                 continue;
    //             }

    //             $runNumber++;

    //             $run = RunWaterbath::create([
    //                 'uuid'             => (string) Str::uuid(),
    //                 'report_uuid'      => $report->uuid,
    //                 'run_number'       => $runNumber,
    //                 'waterbath_number' => $r['waterbath_number'] ?? null,
    //                 'initial_temp'     => $this->num($r['initial_temp'] ?? null),
    //                 'initial_temp_min' => $master?->initial_temp_min,
    //                 'initial_temp_max' => $master?->initial_temp_max,
    //                 'sensor_ok'        => isset($r['sensor_ok']) ? (bool) $r['sensor_ok'] : null,
    //                 'notes'            => $r['notes'] ?? null,
    //             ]);

    //             foreach (array_keys(MasterWaterbathParameter::PARAMETERS) as $key) {
    //                 $row      = $r['parameters'][$key] ?? [];
    //                 $std      = $standards->get($key);
    //                 $isDrying = $key === MasterWaterbathParameter::DRYING;

    //                 RunParameterWaterbath::create([
    //                     'uuid'      => (string) Str::uuid(),
    //                     'run_uuid'  => $run->uuid,
    //                     'parameter' => $key,

    //                     // snapshot standar
    //                     'set_temp_min'      => $std?->set_temp_min,
    //                     'set_temp_max'      => $std?->set_temp_max,
    //                     'cold_zone_set_min' => $isDrying ? $std?->cold_zone_temp_min : null,
    //                     'cold_zone_set_max' => $isDrying ? $std?->cold_zone_temp_max : null,
    //                     'set_time'          => $std?->set_time,
    //                     'std_ct_min'        => $std?->std_ct_min,
    //                     'std_ct_max'        => $std?->std_ct_max,

    //                     // hasil aktual
    //                     'act_temp'      => $this->num($row['act_temp'] ?? null),
    //                     'cold_zone_act' => $isDrying ? $this->num($row['cold_zone_act'] ?? null) : null,
    //                     'act_time'      => $this->num($row['act_time'] ?? null),
    //                     'act_ct'        => $this->num($row['act_ct'] ?? null),
    //                 ]);
    //             }
    //         }
    //     });

    //     return redirect()->route('report_waterbaths.index')
    //         ->with('success', 'Report Waterbath berhasil disimpan');
    // }

    /* ---------------- private helpers ---------------- */

    /**
     * Sementara: area yang masih memakai form lama (tanpa master data).
     * Hapus method ini + storeLegacy() saat format Cikande 1 selesai dibuat.
     */
    private function usesLegacyForm(): bool
    {
        // TODO: sesuaikan cara mengenali Cikande 1 (uuid area / nama area)
        return Auth::user()->area?->area === 'Cikande 1';
    }

    public function create()
    {
        return view('report_waterbaths.v2.create', [
            'products'    => Product::uniqueByName(),
            'formVersion' => $this->formVersionForUser(),
        ]);
    }

    /** 3 = format Cikande 1 (tanpa master data), 2 = format master data (area lain) */
    private function formVersionForUser(): int
    {
        $name = Area::where('uuid', Auth::user()->area_uuid)->value('name');

        // TODO: lebih aman dibandingkan dengan uuid area (atau config) daripada nama
        return str_contains(strtolower(preg_replace('/\s+/', ' ', trim((string) $name))), 'cikande 1') ? 3 : 2;
    }

    public function store(Request $request)
    {
        $version = $this->formVersionForUser();
        $isQc    = auth()->user()->hasRole('QC Inspector');

        $data = $request->validate($this->rulesFor($version));

        $shift = $isQc
            ? session('shift_number') . '-' . session('shift_group')
            : ($data['shift'] ?? 'NON-SHIFT');

        DB::transaction(function () use ($data, $shift, $version) {
            $report = ReportWaterbath::create([
                'uuid'         => (string) Str::uuid(),
                'area_uuid'    => Auth::user()->area_uuid,
                'date'         => $data['date'],
                'shift'        => $shift,
                'form_version' => $version,
                'product_uuid' => $data['product_uuid'],
                'gramase'      => $this->num($data['gramase'] ?? null),
                'created_by'   => Auth::user()->name,
            ]);

            if (filled($data['batch_code'] ?? null)) {
                BatchWaterbath::create([
                    'uuid'        => (string) Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'batch_code'  => $data['batch_code'],
                ]);
            }

            // Versi 2: snapshot standar dari master (server). Versi 3: tidak ada standar.
            $std = $version < 3 ? $this->standardsFromMaster($data['product_uuid']) : null;

            $runNumber = 0;

            foreach (array_values($data['runs']) as $idx => $r) {
                if ($idx > 0 && $this->runIsEmpty($r)) {
                    continue;
                }

                $runNumber++;

                $run = RunWaterbath::create([
                    'uuid'             => (string) Str::uuid(),
                    'report_uuid'      => $report->uuid,
                    'run_number'       => $runNumber,
                    'waterbath_number' => $r['waterbath_number'] ?? null,
                    'initial_temp'     => $this->num($r['initial_temp'] ?? null),
                    'initial_temp_min' => $std['initial_min'] ?? null,
                    'initial_temp_max' => $std['initial_max'] ?? null,
                    'sensor_ok'        => isset($r['sensor_ok']) ? (bool) $r['sensor_ok'] : null,
                    'notes'            => $r['notes'] ?? null,
                ]);

                if ($version >= 3) {
                    $this->saveRunSteps($run, $r['steps'] ?? []);
                } else {
                    $this->saveRunParameters($run, $r['parameters'] ?? [], $std['parameters']);
                }
            }
        });

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Report Waterbath berhasil disimpan');
    }

    private function rulesV2(): array
    {
        return [
            'date'         => 'required|date',
            'shift'        => 'nullable|string|max:20',
            'product_uuid' => 'required|string',
            'gramase'      => 'nullable|numeric|min:0',
            'batch_code'   => 'nullable|string|max:50',

            'runs'                    => 'required|array|min:1',
            'runs.*.uuid'             => 'nullable|string',
            'runs.*.waterbath_number' => 'nullable|string|max:20',
            'runs.*.initial_temp'     => 'nullable|numeric',
            'runs.*.sensor_ok'        => 'nullable|in:0,1',
            'runs.*.notes'            => 'nullable|string|max:2000',

            'runs.*.parameters.*.act_temp'      => 'nullable|numeric',
            'runs.*.parameters.*.cold_zone_act' => 'nullable|numeric',
            'runs.*.parameters.*.act_time'      => 'nullable|numeric|min:0',
            'runs.*.parameters.*.act_ct'        => 'nullable|numeric',
        ];
    }

    private function rulesFor(int $version): array
    {
        $rules = $this->rulesV2();

        if ($version >= 3) {
            // Cikande 1 tidak memakai kolom Act master data
            foreach (['act_temp', 'cold_zone_act', 'act_time', 'act_ct'] as $k) {
                unset($rules["runs.*.parameters.*.$k"]);
            }

            $rules += [
                'runs.*.steps.*.start_time'                    => 'nullable|date_format:H:i',
                'runs.*.steps.*.stop_time'                     => 'nullable|date_format:H:i',
                'runs.*.steps.*.initial_water_temp'            => 'nullable|numeric',
                'runs.*.steps.*.water_temp_after_input_panel'  => 'nullable|numeric',
                'runs.*.steps.*.water_temp_after_input_thermo' => 'nullable|numeric',
                'runs.*.steps.*.water_temp_final'              => 'nullable|numeric',
                'runs.*.steps.*.product_temp_final'            => 'nullable|numeric',
                'runs.*.steps.*.remark'                        => 'nullable|string|max:255',
            ];
        }

        return $rules;
    }

    /** Simpan tabel B format Cikande 1; kolom "Tidak untuk diisi" dipaksa null */
    private function saveRunSteps(RunWaterbath $run, array $rows): void
    {
        $existing = $run->steps->keyBy('parameter');

        foreach (array_keys(RunStepWaterbath::PARAMETERS) as $key) {
            $row    = $rows[$key] ?? [];
            $values = [];

            foreach (array_keys(RunStepWaterbath::FIELDS) as $field) {
                $v = RunStepWaterbath::allows($key, $field) ? ($row[$field] ?? null) : null;

                $values[$field] = match (true) {
                    in_array($field, ['start_time', 'stop_time'], true) => filled($v) ? $v : null,
                    $field === 'remark'                                  => filled($v) ? trim($v) : null,
                    default                                              => $this->num($v),
                };
            }

            if ($step = $existing->get($key)) {
                $step->update($values);
            } else {
                RunStepWaterbath::create($values + [
                    'uuid'      => (string) Str::uuid(),
                    'run_uuid'  => $run->uuid,
                    'parameter' => $key,
                ]);
            }
        }
    }

    private function runIsEmpty(array $run): bool
    {
        foreach (['waterbath_number', 'initial_temp', 'notes'] as $f) {
            if (filled($run[$f] ?? null)) {
                return false;
            }
        }

        foreach (($run['parameters'] ?? []) as $p) {
            foreach (['act_temp', 'cold_zone_act', 'act_time', 'act_ct'] as $f) {
                if (filled($p[$f] ?? null)) {
                    return false;
                }
            }
        }

        foreach (($run['steps'] ?? []) as $s) {
            foreach (array_keys(RunStepWaterbath::FIELDS) as $f) {
                if (filled($s[$f] ?? null)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function num($value): ?float
    {
        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private function storeLegacy(Request $request)
    {
        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        // Simpan header laporan (report_waterbaths)
        $report = ReportWaterbath::create([
            'uuid'              => Str::uuid(),
            'area_uuid'         => Auth::user()->area_uuid,
            'date'              => $request->date,
            'shift'             => $shift,
            'created_by'        => Auth::user()->name,
        ]);

        // Simpan detail_waterbaths
        if ($request->has('details')) {
            foreach ($request->details as $d) {
                DetailWaterbath::create([
                    'uuid'        => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid'=> $d['product_uuid'] ?? null,
                    'batch_code'  => $d['batch_code'] ?? null,
                    'amount'      => $d['amount'] ?? null,
                    'unit'        => $d['unit'] ?? null,
                    'note'        => $d['note'] ?? null,
                    'gramase'     => $d['gramase'] ?? null,
                ]);
            }
        }

        // Simpan pasteurisasi_waterbaths
        if ($request->has('pasteurisasi')) {
            foreach ($request->pasteurisasi as $p) {
                PasteurisasiWaterbath::create([
                    'uuid'                         => Str::uuid(),
                    'report_uuid'                  => $report->uuid,
                    'initial_product_temp'         => $p['initial_product_temp'] ?? null,
                    'initial_water_temp'           => $p['initial_water_temp'] ?? null,
                    'start_time_pasteur'           => $p['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'            => $p['stop_time_pasteur'] ?? null,
                    'water_temp_after_input_panel' => $p['water_temp_after_input_panel'] ?? null,
                    'water_temp_after_input_actual'=> $p['water_temp_after_input_actual'] ?? null,
                    'water_temp_setting'           => $p['water_temp_setting'] ?? null,
                    'water_temp_actual'            => $p['water_temp_actual'] ?? null,
                    'water_temp_final'             => $p['water_temp_final'] ?? null,
                    'product_temp_final'           => $p['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan cooling_shock_waterbaths
        if ($request->has('cooling_shocks')) {
            foreach ($request->cooling_shocks as $c) {
                CoolingShockWaterbath::create([
                    'uuid'              => Str::uuid(),
                    'report_uuid'       => $report->uuid,
                    'initial_water_temp'=> $c['initial_water_temp'] ?? null,
                    'start_time_pasteur'=> $c['start_time_pasteur'] ?? null,
                    'stop_time_pasteur' => $c['stop_time_pasteur'] ?? null,
                    'water_temp_setting'=> $c['water_temp_setting'] ?? null,
                    'water_temp_actual' => $c['water_temp_actual'] ?? null,
                    'water_temp_final' => $c['water_temp_final'] ?? null,
                    'product_temp_final'=> $c['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan dripping_waterbaths
        if ($request->has('drippings')) {
            foreach ($request->drippings as $dr) {
                DrippingWaterbath::create([
                    'uuid'                => Str::uuid(),
                    'report_uuid'         => $report->uuid,
                    'start_time_pasteur'  => $dr['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'   => $dr['stop_time_pasteur'] ?? null,
                    'hot_zone_temperature'=> $dr['hot_zone_temperature'] ?? null,
                    'cold_zone_temperature'=> $dr['cold_zone_temperature'] ?? null,
                    'product_temp_final'  => $dr['product_temp_final'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Report Waterbath berhasil disimpan');
    }

    public function destroy($uuid)
    {
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();
        $isAudit = $report->is_audit;

        DB::transaction(fn () => $report->delete());

        return redirect()
            ->route($isAudit ? 'report_waterbaths.audit' : 'report_waterbaths.index')
            ->with('success', 'Report Waterbath berhasil dihapus');
    }

    public function known($id)
    {
        $report = ReportWaterbath::findOrFail($id);
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
        $report = ReportWaterbath::findOrFail($id);
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
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();
        $isV2   = (int) $report->form_version >= 2;

        $report->load($isV2
            ? ['area', 'product', 'batches', 'runs.parameters', 'runs.steps']
            : ['area', 'details.product', 'pasteurisasi', 'coolingShocks', 'drippings']
        );

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

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_waterbaths');

        $pdf = Pdf::loadView(
            $isV2 ? 'report_waterbaths.export_pdf_v2' : 'report_waterbaths.export_pdf',
            [
                'report'     => $report,
                'createdQr'  => $createdQrBase64,
                'approvedQr' => $approvedQrBase64,
                'knownQr'    => $knownQrBase64,
                'formNumber' => $formNumber,
            ]
        )->setPaper('a4', $isV2 ? 'portrait' : 'landscape');

        $filename = 'report-waterbath-' . Str::slug($report->date . '-shift-' . $report->shift) . '.pdf';

        return $pdf->stream($filename);
    }

    public function addDetail($reportUuid)
    {
        $report = ReportWaterbath::where('uuid', $reportUuid)->firstOrFail();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        // Benar: dua string terpisah
        return view('report_waterbaths.add_detail', compact('report', 'products'));

    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportWaterbath::where('uuid', $reportUuid)->firstOrFail();

        // Simpan detail_waterbaths
        if ($request->has('details')) {
            foreach ($request->details as $d) {
                DetailWaterbath::create([
                    'uuid'        => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid'=> $d['product_uuid'] ?? null,
                    'batch_code'  => $d['batch_code'] ?? null,
                    'amount'      => $d['amount'] ?? null,
                    'unit'        => $d['unit'] ?? null,
                    'note'        => $d['note'] ?? null,
                    'gramase'     => $d['gramase'] ?? null,
                ]);
            }
        }

        // Simpan pasteurisasi_waterbaths
        if ($request->has('pasteurisasi')) {
            foreach ($request->pasteurisasi as $p) {
                PasteurisasiWaterbath::create([
                    'uuid'                         => Str::uuid(),
                    'report_uuid'                  => $report->uuid,
                    'initial_product_temp'         => $p['initial_product_temp'] ?? null,
                    'initial_water_temp'           => $p['initial_water_temp'] ?? null,
                    'start_time_pasteur'           => $p['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'            => $p['stop_time_pasteur'] ?? null,
                    'water_temp_after_input_panel' => $p['water_temp_after_input_panel'] ?? null,
                    'water_temp_after_input_actual'=> $p['water_temp_after_input_actual'] ?? null,
                    'water_temp_setting'           => $p['water_temp_setting'] ?? null,
                    'water_temp_actual'            => $p['water_temp_actual'] ?? null,
                    'water_temp_final'             => $p['water_temp_final'] ?? null,
                    'product_temp_final'           => $p['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan cooling_shock_waterbaths
        if ($request->has('cooling_shocks')) {
            foreach ($request->cooling_shocks as $c) {
                CoolingShockWaterbath::create([
                    'uuid'              => Str::uuid(),
                    'report_uuid'       => $report->uuid,
                    'initial_water_temp'=> $c['initial_water_temp'] ?? null,
                    'start_time_pasteur'=> $c['start_time_pasteur'] ?? null,
                    'stop_time_pasteur' => $c['stop_time_pasteur'] ?? null,
                    'water_temp_setting'=> $c['water_temp_setting'] ?? null,
                    'water_temp_actual' => $c['water_temp_actual'] ?? null,
                    'water_temp_final' => $c['water_temp_final'] ?? null,
                    'product_temp_final'=> $c['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan dripping_waterbaths
        if ($request->has('drippings')) {
            foreach ($request->drippings as $dr) {
                DrippingWaterbath::create([
                    'uuid'                => Str::uuid(),
                    'report_uuid'         => $report->uuid,
                    'start_time_pasteur'  => $dr['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'   => $dr['stop_time_pasteur'] ?? null,
                    'hot_zone_temperature'=> $dr['hot_zone_temperature'] ?? null,
                    'cold_zone_temperature'=> $dr['cold_zone_temperature'] ?? null,
                    'product_temp_final'  => $dr['product_temp_final'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Detail tambahan berhasil disimpan');
    }

    public function edit($uuid)
    {
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();

        // Form baru (all area, berbasis master)
        if ((int) $report->form_version >= 2) {
            $report->load(['product', 'batches', 'runs.parameters', 'runs.steps']);

            $products = Product::uniqueByName();

            // dropdown berisi MIN(uuid) per nama, jadi cocokkan lewat nama produk
            $selectedProduct = $products->firstWhere('product_name', $report->product?->product_name)?->uuid
                ?? $report->product_uuid;

            return view('report_waterbaths.v2.edit', compact('report', 'products', 'selectedProduct'));
        }

        // ---- form lama: tidak diubah ----
        $details = DetailWaterbath::where('report_uuid', $uuid)->get();
        $pasteurisasi = PasteurisasiWaterbath::where('report_uuid', $uuid)->get();
        $cooling_shocks = CoolingShockWaterbath::where('report_uuid', $uuid)->get();
        $drippings = DrippingWaterbath::where('report_uuid', $uuid)->get();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name, MAX(shelf_life) as shelf_life, MAX(created_at) as created_at')
            ->groupBy('product_name')
            ->get();

        return view('report_waterbaths.edit', compact(
            'report', 'details', 'pasteurisasi', 'cooling_shocks', 'drippings', 'products'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();

        if ((int) $report->form_version < 2) {
            return $this->updateLegacy($request, $uuid);
        }

        $version = (int) $report->form_version; // 2 = master data, 3 = Cikande 1

        $rules = $this->rulesFor($version);
        $rules['product_uuid'] = [
            'required', 'string',
            function ($attr, $value, $fail) {
                if (!Product::where('uuid', $value)->exists()) {
                    $fail('Produk tidak valid.');
                }
            },
        ];
        $data = $request->validate($rules);

        $report->load(['product', 'runs.parameters', 'runs.steps']);

        // produk dianggap berubah kalau namanya beda (uuid MIN() bisa bergeser walau produknya sama)
        $newName        = Product::where('uuid', $data['product_uuid'])->value('product_name');
        $productChanged = $newName !== $report->product?->product_name;

        // Hanya versi 2 yang punya standar (snapshot) dari master
        $resnapshot = $productChanged && $version < 3;

        DB::transaction(function () use ($report, $data, $productChanged, $resnapshot, $version) {
            $header = [
                'date'    => $data['date'],
                'gramase' => $this->num($data['gramase'] ?? null),
            ];
            if ($productChanged) {
                $header['product_uuid'] = $data['product_uuid'];
            }
            $report->update($header); // approval tidak disentuh

            // Kode produk (1 batch)
            $batch = $report->batches()->first();
            if (filled($data['batch_code'] ?? null)) {
                $batch
                    ? $batch->update(['batch_code' => $data['batch_code']])
                    : BatchWaterbath::create([
                        'uuid'        => (string) Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'batch_code'  => $data['batch_code'],
                    ]);
            } elseif ($batch) {
                $batch->delete();
            }

            $existing = $report->runs->keyBy('uuid');

            // Versi 3: tidak ada standar.
            // Versi 2: produk ganti -> standar dari master baru; tidak ganti -> snapshot yang sudah ada.
            $standards = $version >= 3
                ? null
                : ($productChanged
                    ? $this->standardsFromMaster($data['product_uuid'])
                    : $this->standardsForReport($report));

            // 1) hapus blok yang dibuang user
            $posted = collect($data['runs'])->pluck('uuid')->filter()->all();
            $existing->reject(fn ($run, $uuid) => in_array($uuid, $posted, true))
                ->each(function (RunWaterbath $run) {
                    $run->parameters()->delete();
                    $run->steps()->delete();
                    $run->delete();
                });

            // 2) update yang ada / buat yang baru
            $number = 0;

            foreach (array_values($data['runs']) as $r) {
                $run = $existing->get($r['uuid'] ?? '');

                // blok baru yang kosong tidak disimpan
                if (!$run && $this->runIsEmpty($r)) {
                    continue;
                }

                $number++;

                $attrs = [
                    'run_number'       => $number,
                    'waterbath_number' => $r['waterbath_number'] ?? null,
                    'initial_temp'     => $this->num($r['initial_temp'] ?? null),
                    'sensor_ok'        => isset($r['sensor_ok']) ? (bool) $r['sensor_ok'] : null,
                    'notes'            => $r['notes'] ?? null,
                ];

                if ($run) {
                    if ($resnapshot) {
                        $attrs['initial_temp_min'] = $standards['initial_min'];
                        $attrs['initial_temp_max'] = $standards['initial_max'];
                    }
                    $run->update($attrs);
                } else {
                    $run = RunWaterbath::create($attrs + [
                        'uuid'             => (string) Str::uuid(),
                        'report_uuid'      => $report->uuid,
                        'initial_temp_min' => $standards['initial_min'] ?? null,
                        'initial_temp_max' => $standards['initial_max'] ?? null,
                    ]);
                }

                if ($version >= 3) {
                    $this->saveRunSteps($run, $r['steps'] ?? []);
                } else {
                    $this->saveRunParameters($run, $r['parameters'] ?? [], $standards['parameters'], $resnapshot);
                }
            }
        });

        return redirect()
            ->route($report->is_audit ? 'report_waterbaths.audit' : 'report_waterbaths.index')
            ->with('success', $report->is_audit
                ? 'Data audit berhasil diperbarui.'
                : 'Data berhasil diperbarui.');
    }

    private function updateLegacy(Request $request, $uuid)
    {
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();

        // Update header
        $report->update([
            'date'  => $request->date,
            'shift' => $request->shift,
        ]);

        // Hapus detail lama untuk replace dengan yang baru
        DetailWaterbath::where('report_uuid', $uuid)->delete();
        PasteurisasiWaterbath::where('report_uuid', $uuid)->delete();
        CoolingShockWaterbath::where('report_uuid', $uuid)->delete();
        DrippingWaterbath::where('report_uuid', $uuid)->delete();

        // Simpan detail baru
        if ($request->has('details')) {
            foreach ($request->details as $i => $d) {
                DetailWaterbath::create([
                    'uuid'        => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid'=> $d['product_uuid'] ?? null,
                    'batch_code'  => $d['batch_code'] ?? null,
                    'amount'      => $d['amount'] ?? null,
                    'unit'        => $d['unit'] ?? null,
                    'note'        => $d['note'] ?? null,
                    'gramase'     => $d['gramase'] ?? null,
                ]);
            }
        }

        // Simpan pasteurisasi
        if ($request->has('pasteurisasi')) {
            foreach ($request->pasteurisasi as $p) {
                PasteurisasiWaterbath::create([
                    'uuid'                         => Str::uuid(),
                    'report_uuid'                  => $report->uuid,
                    'initial_product_temp'         => $p['initial_product_temp'] ?? null,
                    'initial_water_temp'           => $p['initial_water_temp'] ?? null,
                    'start_time_pasteur'           => $p['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'            => $p['stop_time_pasteur'] ?? null,
                    'water_temp_after_input_panel' => $p['water_temp_after_input_panel'] ?? null,
                    'water_temp_after_input_actual'=> $p['water_temp_after_input_actual'] ?? null,
                    'water_temp_setting'           => $p['water_temp_setting'] ?? null,
                    'water_temp_actual'            => $p['water_temp_actual'] ?? null,
                    'water_temp_final'             => $p['water_temp_final'] ?? null,
                    'product_temp_final'           => $p['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan cooling_shock
        if ($request->has('cooling_shocks')) {
            foreach ($request->cooling_shocks as $c) {
                CoolingShockWaterbath::create([
                    'uuid'              => Str::uuid(),
                    'report_uuid'       => $report->uuid,
                    'initial_water_temp'=> $c['initial_water_temp'] ?? null,
                    'start_time_pasteur'=> $c['start_time_pasteur'] ?? null,
                    'stop_time_pasteur' => $c['stop_time_pasteur'] ?? null,
                    'water_temp_setting'=> $c['water_temp_setting'] ?? null,
                    'water_temp_actual' => $c['water_temp_actual'] ?? null,
                    'water_temp_final' => $c['water_temp_final'] ?? null,
                    'product_temp_final'=> $c['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan dripping
        if ($request->has('drippings')) {
            foreach ($request->drippings as $dr) {
                DrippingWaterbath::create([
                    'uuid'                => Str::uuid(),
                    'report_uuid'         => $report->uuid,
                    'start_time_pasteur'  => $dr['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'   => $dr['stop_time_pasteur'] ?? null,
                    'hot_zone_temperature'=> $dr['hot_zone_temperature'] ?? null,
                    'cold_zone_temperature'=> $dr['cold_zone_temperature'] ?? null,
                    'product_temp_final'  => $dr['product_temp_final'] ?? null,
                ]);
            }
        }

        return redirect()
        ->route($report->is_audit ? 'report_waterbaths.audit' : 'report_waterbaths.index')
        ->with('success', $report->is_audit
            ? 'Data audit berhasil diperbarui.'
            : 'Data berhasil diperbarui.');
    }

    /* ---------------- helper update v2 ---------------- */

    private function saveRunParameters(RunWaterbath $run, array $rows, array $standards, bool $resnapshot = false): void
    {
        $existing = $run->parameters->keyBy('parameter');
        $empty    = array_fill_keys([
            'set_temp_min', 'set_temp_max', 'cold_zone_set_min', 'cold_zone_set_max',
            'set_time', 'std_ct_min', 'std_ct_max',
        ], null);

        foreach (array_keys(MasterWaterbathParameter::PARAMETERS) as $key) {
            $row      = $rows[$key] ?? [];
            $isDrying = $key === MasterWaterbathParameter::DRYING;
            $snap     = $standards[$key] ?? $empty;

            $act = [
                'act_temp'      => $this->num($row['act_temp'] ?? null),
                'cold_zone_act' => $isDrying ? $this->num($row['cold_zone_act'] ?? null) : null,
                'act_time'      => $this->num($row['act_time'] ?? null),
                'act_ct'        => $this->num($row['act_ct'] ?? null),
            ];

            if ($p = $existing->get($key)) {
                $p->update($resnapshot ? $act + $snap : $act);
            } else {
                RunParameterWaterbath::create($act + $snap + [
                    'uuid'      => (string) Str::uuid(),
                    'run_uuid'  => $run->uuid,
                    'parameter' => $key,
                ]);
            }
        }
    }

    /**
     * Standar untuk run baru saat edit: ikut snapshot run pertama laporan ini.
     * Kalau run pertama tidak punya snapshot (master belum ada saat dibuat), baru ambil dari master.
     */
    private function standardsForReport(ReportWaterbath $report): array
    {
        $first = $report->runs->sortBy('run_number')->first();

        $hasSnapshot = $first && (
            $first->initial_temp_min !== null
            || $first->parameters->contains(fn ($p) => $p->set_temp_min !== null
                || $p->set_time !== null || $p->std_ct_min !== null)
        );

        if ($hasSnapshot) {
            $snap = ['set_temp_min', 'set_temp_max', 'cold_zone_set_min', 'cold_zone_set_max',
                    'set_time', 'std_ct_min', 'std_ct_max'];

            return [
                'initial_min' => $first->initial_temp_min,
                'initial_max' => $first->initial_temp_max,
                'parameters'  => $first->parameters->keyBy('parameter')
                    ->map(fn ($p) => $p->only($snap))->all(),
            ];
        }

        // Laporan dulu dibuat saat master belum ada: coba ambil dari master sekarang
        return $this->standardsFromMaster($report->product_uuid);
    }

    private function standardsFromMaster(?string $productUuid): array
    {
        $master = $productUuid ? MasterWaterbath::findForProduct($productUuid) : null;
        $params = $master ? $master->parameters->keyBy('parameter') : collect();

        return [
            'initial_min' => $master?->initial_temp_min,
            'initial_max' => $master?->initial_temp_max,
            'parameters'  => $params->map(fn ($p) => [
                'set_temp_min'      => $p->set_temp_min,
                'set_temp_max'      => $p->set_temp_max,
                'cold_zone_set_min' => $p->parameter === MasterWaterbathParameter::DRYING ? $p->cold_zone_temp_min : null,
                'cold_zone_set_max' => $p->parameter === MasterWaterbathParameter::DRYING ? $p->cold_zone_temp_max : null,
                'set_time'          => $p->set_time,
                'std_ct_min'        => $p->std_ct_min,
                'std_ct_max'        => $p->std_ct_max,
            ])->all(),
        ];
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

        $reports = ReportWaterbath::operasional()
            ->with([
                // form lama
                'details.product',
                'pasteurisasi',
                'coolingShocks',
                'drippings',
                // form v2
                'product',
                'batches',
                'runs.parameters',
                'runs.steps',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        // Pisahkan per versi form: lama -> sheet "Waterbath", baru -> sheet "Waterbath (Master Data)"
        $legacy  = $reports->filter(fn ($r) => (int) $r->form_version < 2)->values();
        $master  = $reports->filter(fn ($r) => (int) $r->form_version === 2)->values();
        $cikande = $reports->filter(fn ($r) => (int) $r->form_version >= 3)->values();

        $filename = 'Waterbath_'
        . $dateFrom->format('Ymd') . '_'
        . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(
            new WaterbathWorkbookExport($legacy, $master, $cikande, $periodLabel),
            $filename
        );
    }

    public function byProduct(string $productUuid)
    {
        $master = MasterWaterbath::findForProduct($productUuid);

        return response()->json($master ? $master->toStandardArray() : ['found' => false]);
    }

    public function masterStandard(string $productUuid)
    {
        $master = \App\Models\MasterWaterbath::findForProduct($productUuid);

        return response()->json($master ? $master->toStandardArray() : ['found' => false]);
    }
}