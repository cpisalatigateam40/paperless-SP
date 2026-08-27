<?php
// app/Http/Controllers/ReportBoilingTankController.php

namespace App\Http\Controllers;

use App\Models\ReportBoilingTank;
use App\Models\DetailBoilingTank;
use App\Models\BoilingTankCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasSortableReport;
use App\Exports\BoilingTankExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportBoilingTankController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;

    protected string $bulkModel = ReportBoilingTank::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportBoilingTank::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_boiling_tanks.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area',
            'product',
            'details.checks',];
    }

    protected function getBulkExportExtraData($report): array
    {
        // QR CREATED
        $createdInfo = "Diperiksa oleh: {$report->created_by}\nTanggal: "
            . $report->created_at->format('Y-m-d H:i');

        $createdQrImage = QrCode::format('png')
            ->size(150)
            ->generate($createdInfo);

        // QR KNOWN
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";

        $knownQrImage = QrCode::format('png')
            ->size(150)
            ->generate($knownInfo);

        // QR APPROVED
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: "
                . optional($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";

        $approvedQrImage = QrCode::format('png')
            ->size(150)
            ->generate($approvedInfo);

        // MAX CHECKS
        $maxChecks = max(
            $report->details->max(
                fn ($detail) => $detail->checks->count()
            ) ?? 0,
            3
        );

        return [
            'createdQr' => 'data:image/png;base64,' . base64_encode($createdQrImage),

            'knownQr' => 'data:image/png;base64,' . base64_encode($knownQrImage),

            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQrImage),

            'maxChecks' => $maxChecks,
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_boiling_tank';
    }

    public function index(Request $request)
    {
        $query = ReportBoilingTank::with([
            'area',
            'product',
            'details.checks',
        ])->withCount('details');

        // FILTER AREA
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // HEADER
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('line_boiling_tank', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // AREA
                $q->orWhereHas('area', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });

                // PRODUCT
                $q->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('product_name', 'like', "%{$search}%");
                });

                // DETAIL
                $q->orWhereHas('details', function ($dq) use ($search) {
                    $dq->where('kode_produksi', 'like', "%{$search}%");
                });
            });
        }

        // FILTER STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'relation' => 'details',
                'column' => 'kode_produksi',
            ],
        ]);

        // PAGINATION
        $reports = $query
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        // DATA AREA UNTUK ADMIN & SUPERADMIN
        if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            $areas = Area::orderBy('name')->get();
        } else {
            $areas = collect();
        }

        return view(
            'report_boiling_tanks.index',
            compact('reports', 'areas')
        );
    }

    private function sharedFormData(): array
    {
        return [
            'products' => \App\Models\Product::selectRaw('MIN(uuid) as uuid, product_name')
                ->groupBy('product_name')
                ->get(),
        ];
    }

    public function create()
    {
        return view('report_boiling_tanks.form', [
            'report' => null,
            'isEdit' => false,
            ...$this->sharedFormData(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'product_uuid' => ['nullable', 'uuid'],
            'product_code' => ['nullable', 'string'],
            'gramasi' => ['nullable', 'numeric'],
            'line_boiling_tank' => ['nullable', 'string'],
            'waktu_proses_start' => ['nullable', 'date_format:H:i'],
            'waktu_proses_end' => ['nullable', 'date_format:H:i'],
            'link_kurva' => ['nullable', 'string'],

            'details' => ['nullable', 'array'],
            'details.*.kode_produksi' => ['nullable', 'string'],
            'details.*.checks' => ['nullable', 'array'],
            'details.*.checks.*.berat_mentah' => ['nullable', 'numeric'],
            'details.*.checks.*.actual_core_temp' => ['nullable', 'numeric'],
            'details.*.checks.*.berat_matang' => ['nullable', 'numeric'],
            'details.*.checks.*.suhu_after_cooling' => ['nullable', 'numeric'],
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        DB::transaction(function () use ($request, $shift) {
            $report = ReportBoilingTank::create([
                'area_uuid' => Auth::user()->area_uuid ?? null,
                'date' => $request->date,
                'shift' => $shift,
                'product_uuid' => $request->product_uuid,
                'product_code' => $request->product_code,
                'gramasi' => $request->gramasi,
                'line_boiling_tank' => $request->line_boiling_tank,
                'waktu_proses_start' => $request->waktu_proses_start,
                'waktu_proses_end' => $request->waktu_proses_end,
                'status' => $request->action === 'finish' ? 'selesai' : 'draft',
                'link_kurva' => $request->link_kurva,
                'created_by' => Auth::user()->name ?? 'system',
            ]);

            foreach ($request->details ?? [] as $detailInput) {
                $detail = DetailBoilingTank::create([
                    'report_uuid' => $report->uuid,
                    'kode_produksi' => $detailInput['kode_produksi'] ?? null,
                    'start' => $detailInput['start'] ?? null,
                    'end' => $detailInput['end'] ?? null,
                    'suhu_adonan' => $detailInput['suhu_adonan'] ?? null,
                    'aktual_suhu_tangki_1' => $detailInput['aktual_suhu_tangki_1'] ?? null,
                    'aktual_suhu_tangki_2' => $detailInput['aktual_suhu_tangki_2'] ?? null,
                    'sensori_bentuk' => $detailInput['sensori_bentuk'] ?? null,
                    'sensori_warna' => $detailInput['sensori_warna'] ?? null,
                    'sensori_aroma' => $detailInput['sensori_aroma'] ?? null,
                    'sensori_rasa' => $detailInput['sensori_rasa'] ?? null,
                    'sensori_tekstur' => $detailInput['sensori_tekstur'] ?? null,
                ]);

                $checkCounter = 0;
                foreach ($detailInput['checks'] ?? [] as $checkInput) {
                    $checkCounter++;

                    BoilingTankCheck::create([
                        'detail_uuid' => $detail->uuid,
                        'check_index' => $checkInput['check_index'] ?? $checkCounter,
                        'berat_mentah' => $checkInput['berat_mentah'] ?? null,
                        'actual_core_temp' => $checkInput['actual_core_temp'] ?? null,
                        'berat_matang' => $checkInput['berat_matang'] ?? null,
                        'suhu_after_cooling' => $checkInput['suhu_after_cooling'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('report_boiling_tanks.index')
            ->with('success', 'Laporan Boiling Tank berhasil disimpan');
    }

    public function edit(ReportBoilingTank $report_boiling_tank)
    {
        $report_boiling_tank->load('details.checks');

        return view('report_boiling_tanks.form', [
            'report' => $report_boiling_tank,
            'isEdit' => true,
            ...$this->sharedFormData(),
        ]);
    }

    public function update(Request $request, ReportBoilingTank $report_boiling_tank)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'action' => ['nullable', 'in:draft,finish'],
            'details' => ['nullable', 'array'],
            'details.*.uuid' => ['nullable', 'uuid'],
            'details.*.checks.*.uuid' => ['nullable', 'uuid'],
            'details.*.checks.*.berat_mentah' => ['nullable', 'numeric'],
            'details.*.checks.*.actual_core_temp' => ['nullable', 'numeric'],
            'details.*.checks.*.berat_matang' => ['nullable', 'numeric'],
            'details.*.checks.*.suhu_after_cooling' => ['nullable', 'numeric'],
        ]);

        DB::transaction(function () use ($request, $report_boiling_tank) {
            $report_boiling_tank->update([
                'date' => $request->date,
                'product_uuid' => $request->product_uuid,
                'product_code' => $request->product_code,
                'gramasi' => $request->gramasi,
                'line_boiling_tank' => $request->line_boiling_tank,
                'waktu_proses_start' => $request->waktu_proses_start,
                'waktu_proses_end' => $request->waktu_proses_end,
                'link_kurva' => $request->link_kurva,
            ]);

            $submittedDetailUuids = collect($request->details ?? [])
                ->pluck('uuid')
                ->filter()
                ->all();

            // Hapus Kode Produksi yang sudah tidak ada di request (checks-nya ikut
            // terhapus otomatis lewat cascadeOnDelete di FK detail_uuid)
            $report_boiling_tank->details()
                ->when(!empty($submittedDetailUuids), fn ($q) => $q->whereNotIn('uuid', $submittedDetailUuids))
                ->when(empty($submittedDetailUuids), fn ($q) => $q) // request kosong -> hapus semua detail
                ->delete();

            foreach ($request->details ?? [] as $detailInput) {
                $detailData = [
                    'report_uuid' => $report_boiling_tank->uuid,
                    'kode_produksi' => $detailInput['kode_produksi'] ?? null,
                    'start' => $detailInput['start'] ?? null,
                    'end' => $detailInput['end'] ?? null,
                    'suhu_adonan' => $detailInput['suhu_adonan'] ?? null,
                    'aktual_suhu_tangki_1' => $detailInput['aktual_suhu_tangki_1'] ?? null,
                    'aktual_suhu_tangki_2' => $detailInput['aktual_suhu_tangki_2'] ?? null,
                    'sensori_bentuk' => $detailInput['sensori_bentuk'] ?? null,
                    'sensori_warna' => $detailInput['sensori_warna'] ?? null,
                    'sensori_aroma' => $detailInput['sensori_aroma'] ?? null,
                    'sensori_rasa' => $detailInput['sensori_rasa'] ?? null,
                    'sensori_tekstur' => $detailInput['sensori_tekstur'] ?? null,
                ];

                if (!empty($detailInput['uuid'])) {
                    $detail = DetailBoilingTank::where('uuid', $detailInput['uuid'])->firstOrFail();
                    $detail->update($detailData);
                } else {
                    $detail = DetailBoilingTank::create($detailData);
                }

                $submittedCheckUuids = collect($detailInput['checks'] ?? [])
                    ->pluck('uuid')
                    ->filter()
                    ->all();

                // Hapus Pemeriksaan yang sudah tidak ada di request untuk detail ini
                $detail->checks()
                    ->when(!empty($submittedCheckUuids), fn ($q) => $q->whereNotIn('uuid', $submittedCheckUuids))
                    ->when(empty($submittedCheckUuids), fn ($q) => $q)
                    ->delete();

                $checkCounter = 0;
                foreach ($detailInput['checks'] ?? [] as $checkInput) {
                    $checkCounter++;
                    $checkIndex = $checkInput['check_index'] ?? $checkCounter;

                    if (!empty($checkInput['uuid'])) {
                        $check = BoilingTankCheck::where('uuid', $checkInput['uuid'])->firstOrFail();
                        $check->update([
                            'check_index' => $checkIndex,
                            'berat_mentah' => $checkInput['berat_mentah'] ?? $check->berat_mentah,
                            'actual_core_temp' => $checkInput['actual_core_temp'] ?? $check->actual_core_temp,
                            'berat_matang' => $checkInput['berat_matang'] ?? $check->berat_matang,
                            'suhu_after_cooling' => $checkInput['suhu_after_cooling'] ?? $check->suhu_after_cooling,
                        ]);
                    } else {
                        BoilingTankCheck::create([
                            'detail_uuid' => $detail->uuid,
                            'check_index' => $checkIndex,
                            'berat_mentah' => $checkInput['berat_mentah'] ?? null,
                            'actual_core_temp' => $checkInput['actual_core_temp'] ?? null,
                            'berat_matang' => $checkInput['berat_matang'] ?? null,
                            'suhu_after_cooling' => $checkInput['suhu_after_cooling'] ?? null,
                        ]);
                    }
                }
            }

            if ($request->action === 'finish') {
                $report_boiling_tank->update(['status' => 'selesai']);
            } else {
                $report_boiling_tank->update(['status' => 'draft']);
            }
        });

        return redirect()->route('report_boiling_tanks.index')
            ->with('success', 'Laporan Boiling Tank berhasil diperbarui');
    }

    public function destroy(ReportBoilingTank $report_boiling_tank)
    {
        $report_boiling_tank->delete();

        return redirect()->route('report_boiling_tanks.index')
            ->with('success', 'Laporan Boiling Tank berhasil dihapus');
    }

    public function exportPdf($uuid)
    {
        $report = ReportBoilingTank::with([
            'area',
            'product',
            'details.checks',
        ])->where('uuid', $uuid)->firstOrFail();

        $createdInfo = "Diperiksa oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQr = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQr = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . optional($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQr = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $formNumber = \App\Models\FormNumber::get($report->area->uuid ?? null, 'report_boiling_tanks');

        $maxChecks = max($report->details->max(fn ($d) => $d->checks->count()) ?? 0, 3);

        $pdf = Pdf::loadView('report_boiling_tanks.pdf', [
            'report' => $report,
            'createdQr' => $createdQr,
            'knownQr' => $knownQr,
            'approvedQr' => $approvedQr,
            'formNumber' => $formNumber,
            'maxChecks' => $maxChecks,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('boiling_tank_' . $report->uuid . '.pdf');
    }

    /**
     * Known by
     */
    public function known($id)
    {
        $report = ReportBoilingTank::findOrFail($id);
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
        $report = ReportBoilingTank::findOrFail($id);
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
            $dateFrom    = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $dateTo      = $dateFrom->copy()->endOfMonth();
            $periodLabel = $dateFrom->translatedFormat('F Y');
        } else {
            $dateFrom    = Carbon::parse($request->date_from)->startOfDay();
            $dateTo      = Carbon::parse($request->date_to)->endOfDay();
            $periodLabel = $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y');
        }

        $reports = ReportBoilingTank::with([
                'product',
                'details.checks',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'Boiling_Tank_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(new BoilingTankExport($reports, $periodLabel), $filename);
    }
}