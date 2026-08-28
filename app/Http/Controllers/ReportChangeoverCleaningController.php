<?php

namespace App\Http\Controllers;

use App\Models\DetailChangeoverCleaning;
use App\Models\MasterChecklistItem;
use App\Models\Product;
use App\Models\Area;
use App\Models\Section;
use App\Support\ChangeoverCriteria;
use App\Models\ReportChangeoverCleaning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasBulkApproval;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChangeoverCleaningExport;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;

class ReportChangeoverCleaningController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;

    protected string $bulkModel = ReportChangeoverCleaning::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportChangeoverCleaning::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_changeover_cleanings.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'details.item.section', 'details.product'];
    }

    protected function resolveBulkExportView($report): string
    {
        if ($report->details->whereNotNull('result')->isNotEmpty()) {
            return 'report_changeover_cleanings.pdf_legacy';
        }

        return 'report_changeover_cleanings.pdf';
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

        $extra = [
            'createdQr'  => 'data:image/png;base64,' . base64_encode($createdQr),
            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQr),
            'knownQr'    => 'data:image/png;base64,' . base64_encode($knownQr),
            'formNumber' => \App\Models\FormNumber::get($report->area->uuid, 'report_changeover_cleanings'),
        ];

        $isLegacy = $report->details->whereNotNull('result')->isNotEmpty();

        if ($isLegacy) {
            return $extra; // pdf_legacy.blade.php tidak butuh 'pages'
        }

        // Susun 'pages' persis seperti exportPdf() single
        $pages = [];

        foreach ($report->details as $d) {
            $batchKey = $d->product_uuid . '|' . $d->time;

            if (!isset($pages[$batchKey])) {
                $pages[$batchKey] = [
                    'product'         => $d->product,
                    'time'            => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                    'production_code' => $d->production_code ?? '-',
                    'sisa_bahan'      => [],
                    'mesin_peralatan' => [],
                    'kondisi_ruangan' => [],
                ];
            }

            $pages[$batchKey][$d->group][] = [
                'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                'score'             => $d->score,
                'notes'             => $d->notes,
                'corrective_action' => $d->corrective_action,
            ];
        }

        foreach ($pages as $key => $data) {
            $sectionNames = $report->details
                ->where('group', 'mesin_peralatan')
                ->filter(fn ($d) => ($d->product_uuid . '|' . $d->time) === $key)
                ->pluck('item.section.section_name')
                ->filter()
                ->unique()
                ->implode(', ');

            $pages[$key]['section_names'] = $sectionNames ?: null;
        }

        $extra['pages'] = array_values($pages);

        return $extra;
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_changeover_cleaning';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReportChangeoverCleaning::with([
            'area', 'details.item.section', 'details.product'
        ]);

        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // SEARCH ALL KOLOM
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // AREA
                $q->orWhereHas('area', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });

                // DETAIL
                $q->orWhereHas('details', function ($dq) use ($search) {

                    $dq->where('time', 'like', "%{$search}%")
                        ->orWhere('result', 'like', "%{$search}%")
                        ->orWhere('explanation', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")

                        // NAMA PRODUK
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('product_name', 'like', "%{$search}%");
                        })

                        // ITEM CHECKLIST
                        ->orWhereHas('item', function ($iq) use ($search) {
                            $iq->where('name', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%");
                        });
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
                'column' => 'production_code',
            ],
        ]);

        $reports = $query
            ->paginate(10)
            ->withQueryString();

        if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {

            $areas = Area::orderBy('name')->get();

        } else {

            $areas = collect();
        }

        return view(
            'report_changeover_cleanings.index',
            compact('reports', 'areas')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::orderBy('section_name')->get(); // sudah difilter UserAreaScope

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        $criteria = ChangeoverCriteria::options();
        $criteriaPairs = ChangeoverCriteria::pairs();

        return view('report_changeover_cleanings.form', compact('sections', 'products', 'criteria', 'criteriaPairs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'                                        => 'required|date',
            'batches'                                      => 'required|array|min:1',
            'batches.*.product_uuid'                        => 'required|exists:products,uuid',
            'batches.*.time'                                => 'nullable',
            'batches.*.section_uuid'                        => 'nullable|uuid|exists:sections,uuid',

            'batches.*.machine_items'                       => 'nullable|array',
            'batches.*.machine_items.*.score'               => 'nullable|integer|min:1|max:8',
            'batches.*.machine_items.*.explanation'         => 'nullable|string',
            'batches.*.machine_items.*.notes'               => 'nullable|string',
            'batches.*.machine_items.*.corrective_action'   => 'nullable|string',
            'batches.*.production_code' => 'nullable|string|max:255',

            'batches.*.sisa_bahan_items'                    => 'nullable|array',
            'batches.*.sisa_bahan_items.*.name'             => 'required_with:batches.*.sisa_bahan_items|string|max:255',
            'batches.*.sisa_bahan_items.*.score'            => 'nullable|integer|min:1|max:8',
            'batches.*.sisa_bahan_items.*.explanation'      => 'nullable|string',
            'batches.*.sisa_bahan_items.*.notes'            => 'nullable|string',
            'batches.*.sisa_bahan_items.*.corrective_action'=> 'nullable|string',

            'batches.*.kondisi_ruangan_items'                    => 'nullable|array',
            'batches.*.kondisi_ruangan_items.*.name'             => 'required_with:batches.*.kondisi_ruangan_items|string|max:255',
            'batches.*.kondisi_ruangan_items.*.score'            => 'nullable|integer|min:1|max:8',
            'batches.*.kondisi_ruangan_items.*.explanation'      => 'nullable|string',
            'batches.*.kondisi_ruangan_items.*.notes'            => 'nullable|string',
            'batches.*.kondisi_ruangan_items.*.corrective_action'=> 'nullable|string',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        $report = ReportChangeoverCleaning::create([
            'uuid'        => Str::uuid(),
            'area_uuid'   => Auth::user()->area_uuid,
            'date'        => $request->date,
            'shift'       => $shift,
            'created_by'  => Auth::user()->name,
            'known_by'    => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->batches as $batch) {
            $this->storeGroup($report->uuid, $batch, 'machine_items', 'mesin_peralatan', useItemUuid: true);
            $this->storeGroup($report->uuid, $batch, 'sisa_bahan_items', 'sisa_bahan', useItemUuid: false);
            $this->storeGroup($report->uuid, $batch, 'kondisi_ruangan_items', 'kondisi_ruangan', useItemUuid: false);
        }

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    /**
     * Simpan satu kelompok detail (mesin_peralatan / sisa_bahan / kondisi_ruangan)
     */
    private function storeGroup(string $reportUuid, array $batch, string $sourceKey, string $group, bool $useItemUuid): void
    {
        $rows = $batch[$sourceKey] ?? [];

        if ($useItemUuid) {
            foreach ($rows as $itemUuid => $data) {
                DetailChangeoverCleaning::create([
                    'uuid'               => Str::uuid(),
                    'report_uuid'        => $reportUuid,
                    'group'              => $group,
                    'item_uuid'          => $itemUuid,
                    'item_name'          => null,
                    'product_uuid'       => $batch['product_uuid'],
                    'time'               => $batch['time'] ?? null,
                    'production_code'    => $batch['production_code'] ?? null,
                    'score'              => $data['score'] ?? null,
                    'explanation'        => $data['explanation'] ?? null,
                    'notes'              => $data['notes'] ?? null,
                    'corrective_action'  => $data['corrective_action'] ?? null,
                ]);
            }
        } else {
            foreach ($rows as $data) {
                if (empty($data['name'])) {
                    continue;
                }

                DetailChangeoverCleaning::create([
                    'uuid'               => Str::uuid(),
                    'report_uuid'        => $reportUuid,
                    'group'              => $group,
                    'item_uuid'          => null,
                    'item_name'          => $data['name'],
                    'product_uuid'       => $batch['product_uuid'],
                    'time'               => $batch['time'] ?? null,
                    'production_code'    => $batch['production_code'] ?? null,
                    'score'              => $data['score'] ?? null,
                    'explanation'        => $data['explanation'] ?? null,
                    'notes'              => $data['notes'] ?? null,
                    'corrective_action'  => $data['corrective_action'] ?? null,
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $report = ReportChangeoverCleaning::with(['area', 'details.item', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('report_changeover_cleanings.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $report = ReportChangeoverCleaning::with('details')->where('uuid', $uuid)->firstOrFail();
        $sections = Section::orderBy('section_name')->get();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')->groupBy('product_name')->get();
        $criteria = ChangeoverCriteria::options();
        $criteriaPairs = ChangeoverCriteria::pairs();

        return view('report_changeover_cleanings.form', compact('report', 'sections', 'products', 'criteria', 'criteriaPairs'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportChangeoverCleaning::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'date'                                        => 'required|date',
            'batches'                                      => 'required|array|min:1',
            'batches.*.product_uuid'                        => 'required|exists:products,uuid',
            'batches.*.time'                                => 'nullable',
            'batches.*.section_uuid'                        => 'nullable|uuid|exists:sections,uuid',

            'batches.*.machine_items'                       => 'nullable|array',
            'batches.*.machine_items.*.score'               => 'nullable|integer|min:1|max:8',
            'batches.*.machine_items.*.explanation'         => 'nullable|string',
            'batches.*.machine_items.*.notes'               => 'nullable|string',
            'batches.*.machine_items.*.corrective_action'   => 'nullable|string',
            'batches.*.production_code' => 'nullable|string|max:255',

            'batches.*.sisa_bahan_items'                    => 'nullable|array',
            'batches.*.sisa_bahan_items.*.name'             => 'required_with:batches.*.sisa_bahan_items|string|max:255',
            'batches.*.sisa_bahan_items.*.score'            => 'nullable|integer|min:1|max:8',
            'batches.*.sisa_bahan_items.*.explanation'      => 'nullable|string',
            'batches.*.sisa_bahan_items.*.notes'            => 'nullable|string',
            'batches.*.sisa_bahan_items.*.corrective_action'=> 'nullable|string',

            'batches.*.kondisi_ruangan_items'                    => 'nullable|array',
            'batches.*.kondisi_ruangan_items.*.name'             => 'required_with:batches.*.kondisi_ruangan_items|string|max:255',
            'batches.*.kondisi_ruangan_items.*.score'            => 'nullable|integer|min:1|max:8',
            'batches.*.kondisi_ruangan_items.*.explanation'      => 'nullable|string',
            'batches.*.kondisi_ruangan_items.*.notes'            => 'nullable|string',
            'batches.*.kondisi_ruangan_items.*.corrective_action'=> 'nullable|string',
        ]);

        $report->update([
            'date'  => $request->date,
            'shift' => $request->shift ?? $report->shift,
            'known_by'    => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        $report->details()->delete();

        foreach ($request->batches as $batch) {
            $this->storeGroup($report->uuid, $batch, 'machine_items', 'mesin_peralatan', useItemUuid: true);
            $this->storeGroup($report->uuid, $batch, 'sisa_bahan_items', 'sisa_bahan', useItemUuid: false);
            $this->storeGroup($report->uuid, $batch, 'kondisi_ruangan_items', 'kondisi_ruangan', useItemUuid: false);
        }

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $report = ReportChangeoverCleaning::where('uuid', $uuid)->firstOrFail();
        $report->delete(); // detail ikut terhapus karena FK cascade

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportChangeoverCleaning::with(['area', 'details.item.section', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // QR tetap sama, dipakai kedua versi PDF
        $createdInfo = "Dilaporkan oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $knownInfo = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $approvedInfo = $report->approved_by ? "Diperiksa oleh: {$report->approved_by}" : "Belum diperiksa";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_changeover_cleanings');

        // Deteksi laporan lama: form baru tidak pernah menulis kolom 'result' lagi,
        // jadi kalau ada detail dengan 'result' terisi, ini pasti data sebelum perombakan.
        $isLegacy = $report->details->whereNotNull('result')->isNotEmpty();

        if ($isLegacy) {
            $pdf = Pdf::loadView('report_changeover_cleanings.pdf_legacy', [
                'report'     => $report,
                'createdQr'  => $createdQrBase64,
                'knownQr'    => $knownQrBase64,
                'approvedQr' => $approvedQrBase64,
                'formNumber' => $formNumber,
            ])->setPaper('F4', 'landscape');

            return $pdf->stream('laporan_kebersihan_pergantian_produk_' . $report->date->format('Ymd') . '.pdf');
        }

        // ===== Laporan baru: susun per-batch (1 batch = 1 halaman) =====
        $pages = [];

        foreach ($report->details as $d) {
            $batchKey = $d->product_uuid . '|' . $d->time;

            if (!isset($pages[$batchKey])) {
                $pages[$batchKey] = [
                    'product'         => $d->product,
                    'time'            => $d->time ? \Illuminate\Support\Str::substr($d->time, 0, 5) : '-',
                    'production_code' => $d->production_code ?? '-',
                    'sisa_bahan'      => [],
                    'mesin_peralatan' => [],
                    'kondisi_ruangan' => [],
                ];
            }

            $pages[$batchKey][$d->group][] = [
                'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                'score'             => $d->score,
                'notes'             => $d->notes,
                'corrective_action' => $d->corrective_action,
            ];
        }

        // Ambil nama section unik per batch, dari item-item grup Mesin & Peralatan
        foreach ($pages as $key => $data) {
            $sectionNames = $report->details
                ->where('group', 'mesin_peralatan')
                ->filter(fn ($d) => ($d->product_uuid . '|' . $d->time) === $key)
                ->pluck('item.section.section_name')
                ->filter()
                ->unique()
                ->implode(', ');

            $pages[$key]['section_names'] = $sectionNames ?: null;
        }

        $pages = array_values($pages);

        $pdf = Pdf::loadView('report_changeover_cleanings.pdf', [
            'report'     => $report,
            'pages'      => $pages,
            'createdQr'  => $createdQrBase64,
            'knownQr'    => $knownQrBase64,
            'approvedQr' => $approvedQrBase64,
            'formNumber' => $formNumber,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_kebersihan_pergantian_produk_' . $report->date->format('Ymd') . '.pdf');
    }

    /**
     * Known by
     */
    public function known($id)
    {
        $report = ReportChangeoverCleaning::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah diketahui.'
                );
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil diketahui.'
            );
    }

    /**
     * Approve
     */
    public function approve($id)
    {
        $report = ReportChangeoverCleaning::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah disetujui.'
                );
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil disetujui.'
            );
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

            $dateFrom = Carbon::createFromFormat(
                'Y-m',
                $request->month
            )->startOfMonth();

            $dateTo = $dateFrom->copy()->endOfMonth();

            $periodLabel = $dateFrom->translatedFormat('F Y');

        } else {

            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $dateTo   = Carbon::parse($request->date_to)->endOfDay();

            $periodLabel =
                $dateFrom->format('d/m/Y')
                . ' - '
                . $dateTo->format('d/m/Y');
        }

        $reports = ReportChangeoverCleaning::with([
            'details.item.section',
            'details.product',
            'area'
        ])
        ->where('area_uuid', auth()->user()->area_uuid)
        ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
        ->orderBy('date')
        ->orderBy('shift')
        ->get();

        return Excel::download(
            new ChangeoverCleaningExport(
                $reports,
                $periodLabel
            ),
            'Changeover_Cleaning.xlsx'
        );
    }
}