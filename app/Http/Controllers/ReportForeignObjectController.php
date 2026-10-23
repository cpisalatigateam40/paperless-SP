<?php

namespace App\Http\Controllers;

use App\Models\ReportForeignObject;
use App\Models\DetailForeignObject;
use App\Models\Product;
use App\Models\Section;
use App\Models\RawMaterial;
use App\Models\Premix;
use App\Models\ContaminationCategory;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use App\Exports\ForeignObjectExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use App\Http\Controllers\Traits\HasAuditController;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportForeignObjectController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport, HasAuditController;

    protected function auditModel(): string { return ReportForeignObject::class; }
    protected function auditRoutePrefix(): string { return 'report-foreign-objects'; }
    protected function auditViewPrefix(): string { return 'report_foreign_objects'; }
    protected function auditRelations(): array { return ['area', 'section', 'details.product']; }
    protected function auditSearchColumns(): array { return ['shift', 'created_by']; }
    protected function auditDateColumn(): string { return 'date'; }
    protected function auditTimeColumn(): string { return 'created_at'; }
    protected function auditPlanColumn(): ?string { return null; }

    protected string $bulkModel = ReportForeignObject::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportForeignObject::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_foreign_objects.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'section', 'details.product'];
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
        return 'laporan_foreign_objects';
    }

    public function index(Request $request)
    {
        $query = ReportForeignObject::operasional()->with([
            'area',
            'section',
            'details.product',
            'details.premix',
            'details.rawMaterial',
            'details.contaminationType.category',
        ]);

        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // 🔍 FILTER TANGGAL
        $query->when($request->date, function ($q) use ($request) {
            $q->whereDate('date', $request->date);
        });

        // 🔍 FILTER SHIFT
        $query->when($request->shift, function ($q) use ($request) {
            $q->where('shift', $request->shift);
        });

        // 🔍 GLOBAL SEARCH (HEADER + RELASI + DETAIL)
        $query->when($request->search, function ($q) use ($request) {

            $search = $request->search;

            $q->where(function ($qq) use ($search) {

                // ===== HEADER REPORT =====
                $qq->where('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%");

                // ===== AREA =====
                $qq->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // ===== SECTION =====
                $qq->orWhereHas('section', function ($s) use ($search) {
                    $s->where('section_name', 'like', "%{$search}%");
                });

                // ===== DETAIL FOREIGN OBJECT =====
                $qq->orWhereHas('details', function ($d) use ($search) {
                    $d->where('time', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%")
                        ->orWhere('process_stage', 'like', "%{$search}%")
                        ->orWhere('line', 'like', "%{$search}%")
                        ->orWhere('size_quantity', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('affected_quantity', 'like', "%{$search}%")
                        ->orWhere('investigation', 'like', "%{$search}%")
                        ->orWhere('immediate_correction', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        // kolom legacy (data lama)
                        ->orWhere('contaminant_type', 'like', "%{$search}%")
                        ->orWhere('analysis_stage', 'like', "%{$search}%")
                        ->orWhere('contaminant_origin', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%");
                });

                // ===== PRODUK =====
                $qq->orWhereHas('details.product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%");
                });

                $qq->orWhereHas('details.rawMaterial', fn ($r) => $r->where('material_name', 'like', "%{$search}%"));
                $qq->orWhereHas('details.premix', fn ($p) => $p->where('name', 'like', "%{$search}%"));

                // ===== KATEGORI / TIPE KONTAMINAN =====
                $qq->orWhereHas('details.contaminationType', function ($t) use ($search) {
                    $t->where('name', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"));
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
                'column' => 'production_code',
            ],
        ]);

        $reports = $query->paginate(10)
            ->withQueryString();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_foreign_objects.index', compact('reports', 'areas'));
    }

    private function parseItem(?string $value): array
    {
        if ($value && preg_match('/^(product|raw_material|premix):([0-9a-f-]{36})$/i', $value, $m)) {
            return [strtolower($m[1]), $m[2]];
        }

        return [null, null];
    }


    public function create()
    {
        return view('report_foreign_objects.create', [
            'areas' => Area::all(),
        ] + $this->formData());
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
            $filePath = 'signatures/' . $fileName;

            if (!Storage::disk('public')->exists('signatures')) {
                Storage::disk('public')->makeDirectory('signatures');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }

    private function detailRules(): array
    {
        $actionKeys = array_keys(DetailForeignObject::ACTION_OPTIONS);

        return [
            'details' => 'required|array|min:1',

            'details.*.time' => 'required',
            'details.*.item' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    [$type, $uuid] = $this->parseItem($value);

                    $exists = match ($type) {
                        'product'      => Product::where('uuid', $uuid)->exists(),
                        'raw_material' => RawMaterial::where('uuid', $uuid)->exists(),
                        'premix'       => Premix::where('uuid', $uuid)->exists(),
                        default        => false,
                    };

                    if (! $exists) {
                        $fail('Produk / RM yang dipilih tidak valid.');
                    }
                },
            ],
            'details.*.production_code' => 'nullable|string|max:255',
            'details.*.gramase' => 'nullable|numeric|min:0',
            'details.*.process_stage' => 'nullable|string|max:255',
            'details.*.line' => 'nullable|string|max:255',

            'details.*.contamination_type_uuid' => [
                'nullable',
                'uuid',
                Rule::exists('contamination_types', 'uuid')->whereNull('deleted_at'),
            ],
            'details.*.size_quantity' => 'nullable|string|max:255',
            'details.*.description' => 'nullable|string',

            'details.*.product_status' => ['nullable', Rule::in(array_keys(DetailForeignObject::PRODUCT_STATUS_OPTIONS))],
            'details.*.affected_quantity' => 'nullable|string|max:255',
            'details.*.disposition' => ['nullable', Rule::in($actionKeys)],

            'details.*.investigation' => 'nullable|array|max:5',
            'details.*.investigation.*' => 'nullable|string|max:1000',
            'details.*.immediate_correction' => 'nullable|string',
            'details.*.corrective_action' => 'nullable|string',

            'details.*.evidence' => ['nullable', 'array', 'max:' . DetailForeignObject::MAX_EVIDENCE],
            'details.*.evidence.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'details.*.old_evidence' => 'nullable|array',
            'details.*.old_evidence.*' => 'string',
            'details.*.notes' => 'nullable|string',
        ];
    }

    // Buang baris kosong, rapikan spasi, batasi 5 item, null kalau tidak ada isi
    private function cleanInvestigation($items): ?array
    {
        $items = array_values(array_filter(
            array_map(fn ($v) => trim((string) $v), (array) $items),
            fn ($v) => $v !== ''
        ));

        return $items ? array_slice($items, 0, 5) : null;
    }

    private function detailPayload(array $detail): array
    {
        return [
            'item_type'    => $this->parseItem($detail['item'] ?? null)[0],
            'item_uuid'    => $this->parseItem($detail['item'] ?? null)[1],
            'product_uuid' => $this->parseItem($detail['item'] ?? null)[0] === 'product'
                                ? $this->parseItem($detail['item'])[1]
                                : null,
            'time' => $detail['time'],
            'production_code' => $detail['production_code'] ?? null,
            'gramase' => $detail['gramase'] ?? null,
            'process_stage' => $detail['process_stage'] ?? null,
            'line' => $detail['line'] ?? null,

            'contamination_type_uuid' => $detail['contamination_type_uuid'] ?? null,
            'size_quantity' => $detail['size_quantity'] ?? null,
            'description' => $detail['description'] ?? null,

            'product_status' => $detail['product_status'] ?? null,
            'affected_quantity' => $detail['affected_quantity'] ?? null,
            'disposition' => $detail['disposition'] ?? null,

            'investigation' => $this->cleanInvestigation($detail['investigation'] ?? []),
            'immediate_correction' => $detail['immediate_correction'] ?? null,
            'corrective_action' => $detail['corrective_action'] ?? null,

            'notes' => $detail['notes'] ?? null,
        ];
    }

    private function storeEvidenceFiles($files, $index): array
    {
        $paths = [];

        foreach (array_values((array) $files) as $i => $file) {
            if (! $file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            $filename = time() . "_{$index}_{$i}_" . Str::random(6) . '.' . $file->extension();
            $paths[] = $file->storeAs('evidence_foreign_objects', $filename, 'public');
        }

        return $paths;
    }

    private function createDetails(ReportForeignObject $report, array $details): void
    {
        foreach ($details as $index => $detail) {
            DetailForeignObject::create($this->detailPayload($detail) + [
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'evidence' => $this->storeEvidenceFiles($detail['evidence'] ?? [], $index),
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'sections' => Section::all(),
            'products' => Product::selectRaw('MIN(uuid) as uuid, product_name')
                ->groupBy('product_name')
                ->get(),
            'contaminationCategories' => ContaminationCategory::with('types')
                ->orderBy('sort_order')
                ->get(),
            'actionOptions' => DetailForeignObject::ACTION_OPTIONS,
            'rawMaterials' => RawMaterial::orderBy('material_name')->get(['uuid', 'material_name']),
            'premixes'     => Premix::orderBy('name')->get(['uuid', 'name']),
            'productStatusOptions' => DetailForeignObject::PRODUCT_STATUS_OPTIONS,
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->detailRules() + [
            'date' => 'required|date',
            'section_uuid' => 'required|uuid',
            'notes' => 'nullable|string',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        DB::transaction(function () use ($request, $shift) {
            $report = ReportForeignObject::create([
                'uuid' => Str::uuid(),
                'date' => $request->date,
                'shift' => $shift,
                'notes' => $request->notes,
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $request->section_uuid,
                'created_by' => Auth::user()->name,
            ]);

            $this->createDetails($report, $request->details);
        });

        return redirect()->route('report-foreign-objects.index')->with('success', 'Laporan berhasil disimpan');
    }

    public function createDetail($uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();

        return view('report_foreign_objects.add_detail', ['report' => $report] + $this->formData());
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();

        $request->validate($this->detailRules());

        DB::transaction(function () use ($request, $report) {
            $this->createDetails($report, $request->details);
        });

        return redirect()->route('report-foreign-objects.index')->with('success', 'Detail berhasil ditambahkan');
    }


    public function destroy($uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();

        // Hapus semua file evidence terkait (evidence = array path)
        foreach ($report->details as $detail) {
            Storage::disk('public')->delete(
                array_values(array_filter((array) $detail->evidence, fn ($v) => filled($v)))
            );
        }

        // Hapus semua detail
        $report->details()->delete();

        // Hapus report utama
        $report->delete();

        return redirect()->route('report-foreign-objects.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function known($id)
    {
        $report = ReportForeignObject::findOrFail($id);
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
        $report = ReportForeignObject::findOrFail($id);
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
        $report = ReportForeignObject::with(
            'area',
            'section',
            'details.product',
            'details.rawMaterial',
            'details.premix',
            'details.contaminationType.category'
        )
            ->where('uuid', $uuid)
            ->firstOrFail();

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

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_foreign_objects');

        $pdf = Pdf::loadView('report_foreign_objects.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
            'formNumber' => $formNumber,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Kontaminasi_' . $report->date->format('Ymd') . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportForeignObject::with('details')->where('uuid', $uuid)->firstOrFail();

        return view('report_foreign_objects.edit', ['report' => $report] + $this->formData());
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportForeignObject::with('details')->where('uuid', $uuid)->firstOrFail();

        $request->validate($this->detailRules() + [
            'date' => 'required|date',
            'section_uuid' => 'required|uuid',
            'shift' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'details.*.uuid' => 'nullable|uuid',
        ]);

        foreach ($request->details as $i => $input) {
            $total = count((array) ($input['old_evidence'] ?? [])) + count((array) ($input['evidence'] ?? []));

            if ($total > DetailForeignObject::MAX_EVIDENCE) {
                throw ValidationException::withMessages([
                    "details.{$i}.evidence" => 'Maksimal ' . DetailForeignObject::MAX_EVIDENCE . ' foto per detail.',
                ]);
            }
        }

        DB::transaction(function () use ($request, $report) {
            // Header: field approval (known_by/approved_by/approved_at) sengaja tidak disentuh
            $report->update([
                'date' => $request->date,
                'section_uuid' => $request->section_uuid,
                'shift' => $request->shift ?: $report->shift,
                'notes' => $request->notes,
            ]);

            $existing = $report->details->keyBy('uuid');
            $keptUuids = [];

            foreach ($request->details as $index => $input) {
                $detail = !empty($input['uuid']) ? $existing->get($input['uuid']) : null;
                $payload = $this->detailPayload($input);
                $newPaths = $this->storeEvidenceFiles($input['evidence'] ?? [], $index);

                if ($detail) {
                    $before = $detail->evidence;

                    // hanya path milik detail ini yang boleh dipertahankan
                    $kept = array_values(array_intersect((array) ($input['old_evidence'] ?? []), $before));

                    $detail->update($payload + ['evidence' => array_merge($kept, $newPaths)]);

                    // foto lama yang dihapus user di form
                    Storage::disk('public')->delete(array_values(array_diff($before, $kept)));

                    $keptUuids[] = $detail->uuid;
                } else {
                    $created = DetailForeignObject::create($payload + [
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'evidence' => $newPaths,
                    ]);
                    $keptUuids[] = $created->uuid;
                }
            }

            // Detail yang blok-nya dihapus di form
            foreach ($existing as $detail) {
                if (!in_array($detail->uuid, $keptUuids, true)) {
                    Storage::disk('public')->delete($detail->evidence);
                    $detail->delete();
                }
            }
        });

        return redirect()
        ->route($report->is_audit ? 'report-foreign-objects.audit' : 'report-foreign-objects.index')
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
    
        $reports = ReportForeignObject::operasional()
        ->with(['details.product', 'details.premix', 'details.rawMaterial', 'details.contaminationType.category', 'section'])
        ->where('area_uuid', auth()->user()->area_uuid)
        ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
        ->orderBy('date')
        ->orderBy('shift')
        ->get();
    
        $filename = 'Foreign_Object_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new ForeignObjectExport($reports, $periodLabel), $filename);
    }
}