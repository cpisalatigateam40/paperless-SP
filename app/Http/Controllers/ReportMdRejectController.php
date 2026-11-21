<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ReportMdReject;
use App\Models\ReportMdRejectDetail;
use App\Http\Controllers\Traits\HasAuditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FormNumber;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportMdRejectExport;
use App\Http\Controllers\Traits\HasExcelImport;

class ReportMdRejectController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport, HasExcelImport, HasAuditController;

    protected function getImportModelClass(): string
    {
        return ReportMdReject::class;
    }

    protected function getTemplateFilename(): string
    {
        return 'template-import-md-reject.xlsx';
    }

    // Dipanggil SEKALI per upload; import_batch menandai semua baris dari upload yang sama
    protected function getAdditionalImportData(): array
    {
        $u = auth()->user();

        return [
            'area_uuid'    => $u->area_uuid,
            'created_by'   => $u->name,
            'import_batch' => (string) Str::uuid(),
        ];
    }


    protected string $modelClass = ReportMdReject::class;

    protected string $bulkModel = ReportMdReject::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportMdReject::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report-md-rejects.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'verifications', 'details.product'];
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
            'formNumber' => FormNumber::get($report->area_uuid, 'report_md_rejects'),
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_reject_md';
    }

    protected function auditModel(): string { return ReportMdReject::class; }
    protected function auditRoutePrefix(): string { return 'report_md_rejects'; }
    protected function auditViewPrefix(): string { return 'report-md-rejects'; } // dari @include('report-md-rejects._detail')
    protected function auditRelations(): array { return ['area', 'details.product', 'verifications']; }
    protected function auditSearchColumns(): array { return ['shift', 'line', 'created_by']; }
    protected function auditDateColumn(): string { return 'date'; }
    protected function auditTimeColumn(): string { return 'created_at'; }
    protected function auditPlanColumn(): ?string { return null; }


    public function index(Request $request)
    {
        $query = ReportMdReject::operasional()
            ->with(['area', 'verifications', 'details.product']);

        // FILTER AREA (hanya admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // SEARCH GLOBAL (header + detail + relasi)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                // Header laporan
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('line', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // Area
                $q->orWhereHas('area', fn ($qa) => $qa->where('name', 'like', "%{$search}%"));

                // Detail produk
                $q->orWhereHas('details', function ($qd) use ($search) {
                    $qd->where('production_code', 'like', "%{$search}%")
                        ->orWhere('time', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('suspected_cause_other', 'like', "%{$search}%");
                });

                // Nama produk
                $q->orWhereHas('details.product', fn ($qp) => $qp->where('product_name', 'like', "%{$search}%"));
            });
        }

        // SORTING (dari <x-report-sort>)
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'relation' => 'details',
                'column'   => 'production_code',
            ],
        ]);

        // FILTER TANGGAL LAPORAN
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        $reports = $query->paginate(10)->withQueryString();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? \App\Models\Area::orderBy('name')->get()
            : collect();

        return view('report-md-rejects.index', compact('reports', 'areas'));
    }

    private function productOptions()
    {
        return Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();
    }

    public function create()
    {
        return view('report-md-rejects.create', [
            'products' => $this->productOptions(),
            'testPieces'  => ReportMdReject::TEST_PIECES,
            'causes'      => ReportMdRejectDetail::CAUSES,
            'dispositions'=> ReportMdRejectDetail::DISPOSITIONS,
            'defaultShift'=> $this->defaultShift(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data) {
            $report = ReportMdReject::create([
                'uuid'       => (string) Str::uuid(),
                'area_uuid'  => auth()->user()->area_uuid,
                'date'       => $data['date'],
                'shift'      => $data['shift'],
                'line'       => $data['line'],
                'created_by' => auth()->user()->name,
            ]);

            foreach ($data['verifications'] as $v) {
                $report->verifications()->create($v);
            }

            foreach ($data['details'] ?? [] as $d) {
                $report->details()->create($this->normalizeDetail($d));
            }
        });

        return redirect()->route('report_md_rejects.index')
    ->with('success', 'Laporan berhasil disimpan.');
    }

    public function show(string $uuid)
    {
        $report = $this->findReport($uuid);

        return view('report-md-rejects.show', compact('report'));
    }

    public function edit(string $uuid)
    {
        $report   = $this->findReport($uuid);
        $products = $this->productOptions();

        // Samakan product_uuid tersimpan dengan uuid hasil grup (MIN(uuid)),
        // supaya option-nya terpilih di Select2
        $uuidByName = $products->pluck('uuid', 'product_name');
        foreach ($report->details as $d) {
            if ($d->product && isset($uuidByName[$d->product->product_name])) {
                $d->product_uuid = $uuidByName[$d->product->product_name];
            }
        }

        return view('report-md-rejects.edit', [
            'report'       => $report,
            'products'     => $products,
            'testPieces'   => ReportMdReject::TEST_PIECES,
            'causes'       => ReportMdRejectDetail::CAUSES,
            'dispositions' => ReportMdRejectDetail::DISPOSITIONS,
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportMdReject::where('uuid', $uuid)->firstOrFail();
        $data   = $request->validate($this->rules());

        DB::transaction(function () use ($report, $data) {
            $report->update([
                'date'  => $data['date'],
                'shift' => $data['shift'],
                'line'  => $data['line'],
            ]);

            // Verifikasi: 3 baris tetap, cocokkan berdasarkan test_piece
            foreach ($data['verifications'] as $v) {
                $report->verifications()->updateOrCreate(
                    ['test_piece' => $v['test_piece']],
                    ['result' => $v['result'], 'notes' => $v['notes'] ?? null]
                );
            }

            // Detail: cocokkan berdasarkan uuid (bukan hapus semua lalu buat ulang)
            $incoming = collect($data['details'] ?? []);
            $keepIds  = $incoming->pluck('uuid')->filter()->all();

            $report->details()->whereNotIn('uuid', $keepIds)->delete();

            foreach ($incoming as $d) {
                $payload = $this->normalizeDetail($d);

                if (!empty($d['uuid'])) {
                    $report->details()->where('uuid', $d['uuid'])->update($payload);
                } else {
                    $report->details()->create($payload);
                }
            }
        });

        return redirect()
        ->route($report->is_audit ? 'report_md_rejects.audit' : 'report_md_rejects.index')
        ->with('success', $report->is_audit
            ? 'Data audit berhasil diperbarui.'
            : 'Data berhasil diperbarui.');
        }

    public function destroy(string $uuid)
    {
        $report   = ReportMdReject::where('uuid', $uuid)->firstOrFail();
        $wasAudit = $report->is_audit;

        DB::transaction(function () use ($report) {
            // hapus data operasional -> salinan audit ikut dihapus
            $targets = $report->is_audit
                ? [$report]
                : array_filter([$report, $report->auditVersion]);

            foreach ($targets as $r) {
                $r->details()->delete();
                $r->verifications()->delete();
                $r->delete();
            }
        });

        return redirect()
            ->route($wasAudit ? 'report_md_rejects.audit' : 'report_md_rejects.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function known($id)
    {
        $report = ReportMdReject::findOrFail($id);
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
        $report = ReportMdReject::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    /* ===================== HELPER ===================== */

    private function findReport(string $uuid): ReportMdReject
    {
        return ReportMdReject::with(['verifications', 'details.product', 'area'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    private function defaultShift(): string
    {
        return auth()->user()->hasRole('QC Inspector') && session('shift_number') && session('shift_group')
            ? session('shift_number') . '-' . session('shift_group')
            : 'NON-SHIFT';
    }

    private function rules(): array
    {
        $result = ['required', Rule::in(['detected', 'not_detected'])];

        return [
            'date'  => 'required|date',
            'shift' => 'required|string|max:50',
            'line'  => 'required|string|max:100',

            'verifications'              => 'required|array|size:' . count(ReportMdReject::TEST_PIECES),
            'verifications.*.test_piece' => ['required', Rule::in(ReportMdReject::TEST_PIECES)],
            'verifications.*.result'     => $result,
            'verifications.*.notes'      => 'nullable|string|max:255',

            'details'                         => 'nullable|array',
            'details.*.uuid'                  => 'nullable|string',
            'details.*.product_uuid'          => 'required|exists:products,uuid',
            'details.*.time'                  => 'required|date_format:H:i',
            'details.*.production_code'       => 'required|string|max:100',
            'details.*.reject_qty'            => 'required|integer|min:0',
            'details.*.recheck_result'        => ['required', Rule::in(['detected', 'not_detected'])],
            'details.*.contamination_found'   => 'required|boolean',
            'details.*.suspected_causes'      => 'nullable|array',
            'details.*.suspected_causes.*'    => Rule::in(array_keys(ReportMdRejectDetail::CAUSES)),
            'details.*.suspected_cause_other' => 'nullable|string|max:255',
            'details.*.corrective_action'     => 'nullable|string',
            'details.*.disposition'           => ['required', Rule::in(array_keys(ReportMdRejectDetail::DISPOSITIONS))],
        ];
    }

    private function normalizeDetail(array $d): array
    {
        $causes = array_values($d['suspected_causes'] ?? []);

        return [
            'product_uuid'          => $d['product_uuid'],
            'time'                  => $d['time'],
            'production_code'       => trim($d['production_code']),
            'reject_qty'            => $d['reject_qty'],
            'recheck_result'        => $d['recheck_result'],
            'contamination_found'   => (bool) $d['contamination_found'],
            'suspected_causes'      => $causes,
            // isian "Lainnya" hanya disimpan kalau opsi Lainnya dicentang
            'suspected_cause_other' => in_array('other', $causes, true)
                ? ($d['suspected_cause_other'] ?? null)
                : null,
            'corrective_action'     => $d['corrective_action'] ?? null,
            'disposition'           => $d['disposition'],
        ];
    }

    public function exportPdf(string $uuid)
    {
        $report = $this->findReport($uuid);

        $qr = fn (string $text) => 'data:image/png;base64,'
            . base64_encode(QrCode::format('png')->size(150)->generate($text));

        // QR dari kolom tersimpan di laporan, bukan dari user yang login
        $createdQr = $qr("Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i'));

        $knownQr = $report->known_by
            ? $qr("Diketahui oleh: {$report->known_by}")
            : null;

        $approvedQr = $report->approved_by
            ? $qr("Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i'))
            : null;

        $formNumber = FormNumber::get($report->area_uuid, 'report-md-rejects');

        $pdf = Pdf::loadView('report-md-rejects.pdf', [
            'report'     => $report,
            'createdQr'  => $createdQr,
            'knownQr'    => $knownQr,
            'approvedQr' => $approvedQr,
            'formNumber' => $formNumber,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_md_reject_' . $report->date->format('Y-m-d') . '.pdf');
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

        $reports = ReportMdReject::with(['area', 'verifications', 'details.product'])
            ->where('is_audit', false)
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'MD_Reject_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(new ReportMdRejectExport($reports, $periodLabel), $filename);
    }
}