<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\ReportMtClean;
use App\Models\DetailMtClean;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\HasBulkApproval;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MtCleanExport;
use App\Traits\HasBulkPdfExport;
use App\Models\DetailMtCleanPhoto;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasSortableReport;

class ReportMtCleanController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;

    protected string $bulkModel = ReportMtClean::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportMtClean::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_mt_cleans.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area',
            'details.product'];
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
        return 'laporan_magnet_trap';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReportMtClean::with([
            'area',
            'details.product',
            'details.photos'
        ]);

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

                // Header
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // Area
                $q->orWhereHas('area', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });

                // Detail
                $q->orWhereHas('details', function ($dq) use ($search) {

                    $dq->where('time', 'like', "%{$search}%")
                        ->orWhere('mt_1', 'like', "%{$search}%")
                        ->orWhere('mt_2', 'like', "%{$search}%")
                        ->orWhere('finding_type', 'like', "%{$search}%")
                        ->orWhere('condition', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")

                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('product_name', 'like', "%{$search}%");
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
            'report_mt_cleans.index',
            compact('reports', 'areas')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();

        $products = Product::selectRaw(
                'MIN(uuid) as uuid, product_name'
            )
            ->groupBy('product_name')
            ->get();

        return view(
            'report_mt_cleans.form',
            compact('areas', 'products')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'                         => 'required|date',
            'details'                      => 'required|array|min:1',
            'details.*.product_uuid'       => 'required|exists:products,uuid',
            'details.*.time'               => 'nullable',
            'details.*.mt_1'               => 'nullable|string|max:255',
            'details.*.mt_2'               => 'nullable|string|max:255',
            'details.*.condition'          => 'nullable|string|max:255',
            'details.*.note'               => 'nullable|string',
            'details.*.corrective_action'  => 'nullable|string',
            'details.*.photos.*'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'details.*.metal_weight' => 'nullable|numeric|min:0',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        $report = ReportMtClean::create([
            'uuid'        => Str::uuid(),
            'area_uuid'   => Auth::user()->area_uuid,
            'date'        => $request->date,
            'shift'       => $shift,
            'created_by'  => Auth::user()->name,
            'known_by'    => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        $detailsInput = $request->input('details', []);
        $detailsFiles = $request->file('details', []);

        foreach ($detailsInput as $i => $detail) {
            $detailRecord = DetailMtClean::create([
                'uuid'               => Str::uuid(),
                'report_uuid'        => $report->uuid,
                'product_uuid'       => $detail['product_uuid'],
                'time'               => $detail['time'] ?? null,
                'mt_1'               => $detail['mt_1'] ?? null,
                'mt_2'               => $detail['mt_2'] ?? null,
                'finding_type'       => null, // form baru gak pakai text lagi
                'condition'          => $detail['condition'] ?? null,
                'note'               => $detail['note'] ?? null,
                'corrective_action'  => $detail['corrective_action'] ?? null,
                'metal_weight' => $detail['metal_weight'] ?? null,
            ]);

            if (!empty($detailsFiles[$i]['photos'])) {
                foreach ($detailsFiles[$i]['photos'] as $photo) {
                    if ($photo && $photo->isValid()) {
                        $path = $photo->store('mt_clean_photos', 'public');

                        DetailMtCleanPhoto::create([
                            'uuid'        => Str::uuid(),
                            'detail_uuid' => $detailRecord->uuid,
                            'file_path'   => $path,
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('report_mt_cleans.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $report = ReportMtClean::with([
                'area',
                'details.product'
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view(
            'report_mt_cleans.show',
            compact('report')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $report = ReportMtClean::with('details.photos') // 👈 tambahkan
            ->where('uuid', $uuid)
            ->firstOrFail();

        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_mt_cleans.form', compact('report', 'areas', 'products'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportMtClean::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'date'                          => 'required|date',
            'shift'                         => 'nullable|string|max:255',

            'details'                       => 'required|array|min:1',
            'details.*.uuid'                => 'nullable|string',
            'details.*.product_uuid'        => 'required|exists:products,uuid',
            'details.*.time'                => 'nullable',
            'details.*.mt_1'                => 'nullable|string|max:255',
            'details.*.mt_2'                => 'nullable|string|max:255',
            'details.*.condition'           => 'nullable|string|max:255',
            'details.*.note'                => 'nullable|string',
            'details.*.corrective_action'   => 'nullable|string',
            'details.*.photos.*'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'details.*.deleted_photos'      => 'nullable|array',
            'details.*.deleted_photos.*'    => 'nullable|string',
            'details.*.metal_weight' => 'nullable|numeric|min:0',
        ]);

        $detailsFiles = $request->file('details', []);

        DB::transaction(function () use ($validated, $report, $detailsFiles) {

            $report->update([
                'date'  => $validated['date'],
                'shift' => $validated['shift'] ?? $report->shift,
            ]);

            $submittedUuids = [];

            foreach ($validated['details'] as $i => $detail) {

                $fields = [
                    'product_uuid'       => $detail['product_uuid'],
                    'time'               => $detail['time'] ?? null,
                    'mt_1'               => $detail['mt_1'] ?? null,
                    'mt_2'               => $detail['mt_2'] ?? null,
                    'condition'          => $detail['condition'] ?? null,
                    'note'               => $detail['note'] ?? null,
                    'corrective_action'  => $detail['corrective_action'] ?? null,
                    'metal_weight' => $detail['metal_weight'] ?? null,
                ];

                $detailUuid = $detail['uuid'] ?? null;
                $detailRecord = $detailUuid
                    ? $report->details()->where('uuid', $detailUuid)->first()
                    : null;

                if ($detailRecord) {
                    $detailRecord->update($fields);
                } else {
                    $fields['finding_type'] = null;
                    $detailRecord = $report->details()->create($fields);
                }

                $submittedUuids[] = $detailRecord->uuid;

                if (!empty($detail['deleted_photos'])) {
                    $photosToDelete = DetailMtCleanPhoto::where('detail_uuid', $detailRecord->uuid)
                        ->whereIn('uuid', $detail['deleted_photos'])
                        ->get();

                    foreach ($photosToDelete as $photo) {
                        Storage::disk('public')->delete($photo->file_path);
                        $photo->delete();
                    }
                }

                if (!empty($detailsFiles[$i]['photos'])) {
                    foreach ($detailsFiles[$i]['photos'] as $photo) {
                        if ($photo && $photo->isValid()) {
                            $path = $photo->store('mt_clean_photos', 'public');

                            DetailMtCleanPhoto::create([
                                'uuid'        => Str::uuid(),
                                'detail_uuid' => $detailRecord->uuid,
                                'file_path'   => $path,
                            ]);
                        }
                    }
                }
            }

            $oldDetails = $report->details()->whereNotIn('uuid', $submittedUuids)->get();
            foreach ($oldDetails as $old) {
                foreach ($old->photos as $photo) {
                    Storage::disk('public')->delete($photo->file_path);
                }
                $old->delete();
            }
        });

        return redirect()
            ->route('report_mt_cleans.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $report = ReportMtClean::with('details.photos')
            ->where('uuid', $uuid)
            ->firstOrFail();

        foreach ($report->details as $detail) {
            foreach ($detail->photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
            }
        }

        $report->delete();

        return redirect()
            ->route('report_mt_cleans.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    /**
     * Export PDF
     */
    public function exportPdf($uuid)
    {
        $report = ReportMtClean::with([
            'area',
            'details.product',
            'details.photos'
        ])
        ->where('uuid', $uuid)
        ->firstOrFail();

        $createdInfo =
            "Dibuat oleh: {$report->created_by}\nTanggal: "
            . $report->created_at->format('Y-m-d H:i');

        $createdQrImage = QrCode::format('png')
            ->size(150)
            ->generate($createdInfo);

        $createdQrBase64 =
            'data:image/png;base64,'
            . base64_encode($createdQrImage);

        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";

        $knownQrImage = QrCode::format('png')
            ->size(150)
            ->generate($knownInfo);

        $knownQrBase64 =
            'data:image/png;base64,'
            . base64_encode($knownQrImage);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: "
                . ($report->approved_at
                    ? Carbon::parse($report->approved_at)
                        ->format('Y-m-d H:i')
                    : '-')
            : "Belum disetujui";

        $approvedQrImage = QrCode::format('png')
            ->size(150)
            ->generate($approvedInfo);

        $approvedQrBase64 =
            'data:image/png;base64,'
            . base64_encode($approvedQrImage);

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_mt_cleans');

        $pdf = Pdf::loadView(
            'report_mt_cleans.pdf',
            [
                'report' => $report,
                'createdQr' => $createdQrBase64,
                'knownQr' => $knownQrBase64,
                'approvedQr' => $approvedQrBase64,
                'formNumber' => $formNumber,
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'laporan_mt_clean_'
            . $report->date->format('Ymd')
            . '.pdf'
        );
    }

    /**
     * Known by
     */
    public function known($id)
    {
        $report = ReportMtClean::findOrFail($id);
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
        $report = ReportMtClean::findOrFail($id);
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

            $periodLabel =
                $dateFrom->translatedFormat('F Y');
        } else {

            $dateFrom = Carbon::parse(
                $request->date_from
            )->startOfDay();

            $dateTo = Carbon::parse(
                $request->date_to
            )->endOfDay();

            $periodLabel =
                $dateFrom->format('d/m/Y')
                . ' - '
                . $dateTo->format('d/m/Y');
        }

        $reports = ReportMtClean::with([
                'area',
                'details.product'
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween(
                'date',
                [
                    $dateFrom->toDateString(),
                    $dateTo->toDateString()
                ]
            )
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename =
            'MT_Clean_'
            . $dateFrom->format('Ymd')
            . '_'
            . $dateTo->format('Ymd')
            . '.xlsx';

        return Excel::download(
            new MtCleanExport(
                $reports,
                $periodLabel
            ),
            $filename
        );
    }
}