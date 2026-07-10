<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\DetailStartupLabel;
use App\Models\Product;
use App\Models\ReportStartupLabel;
use App\Models\DetailStartupLabelPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasBulkApproval;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StartupLabelExport;
use App\Traits\HasBulkPdfExport;
use Illuminate\Support\Facades\Storage;

class ReportStartupLabelController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport;
    protected string $bulkModel = ReportStartupLabel::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportStartupLabel::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_startup_labels.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'details.product'];
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
        return 'laporan_startup_label';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReportStartupLabel::with([
            'area',
            'details.product',
            'details.photos',
        ])->latest('date');

        // 🔍 SEARCH HEADER + DETAIL
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 DETAIL STARTUP LABEL
                $q->orWhereHas('details', function ($dq) use ($search) {

                    $dq->where('time', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%")
                        ->orWhere('result', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('best_before', 'like', "%{$search}%")

                        // 🔥 Nama produk dari relasi
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('product_name', 'like', "%{$search}%");
                        });
                });

                // 🔹 AREA
                $q->orWhereHas('area', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'report_startup_labels.index',
            compact('reports')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_startup_labels.form', compact('areas', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'                         => 'required|date',
            'details'                      => 'required|array|min:1',
            'details.*.product_uuid'       => 'required|exists:products,uuid',
            'details.*.time'               => 'nullable',
            'details.*.production_code'    => 'nullable|string|max:255',
            'details.*.best_before'        => 'nullable|date',
            'details.*.result'             => 'nullable|string|max:255',
            'details.*.corrective_action'  => 'nullable|string',
            'details.*.packaging'          => 'nullable|string',
            'details.*.photos.*'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        $report = ReportStartupLabel::create([
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
            $detailRecord = DetailStartupLabel::create([
                'uuid'               => Str::uuid(),
                'report_uuid'        => $report->uuid,
                'product_uuid'       => $detail['product_uuid'],
                'time'               => $detail['time'] ?? null,
                'production_code'    => $detail['production_code'] ?? null,
                'best_before'        => $detail['best_before'] ?? null,
                'result'             => $detail['result'] ?? null,
                'corrective_action'  => $detail['corrective_action'] ?? null,
                'packaging'          => $detail['packaging'] ?? null,
            ]);

            // Simpan foto (jika ada)
            if (!empty($detailsFiles[$i]['photos'])) {
                foreach ($detailsFiles[$i]['photos'] as $photo) {
                    if ($photo && $photo->isValid()) {
                        $path = $photo->store('startup_label_photos', 'public');

                        DetailStartupLabelPhoto::create([
                            'uuid'        => Str::uuid(),
                            'detail_uuid' => $detailRecord->uuid,
                            'file_path'   => $path,
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $report = ReportStartupLabel::with(['area', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('report_startup_labels.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $report = ReportStartupLabel::with('details.photos')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_startup_labels.form', compact('report', 'areas', 'products'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportStartupLabel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'date'                          => 'required|date',
            'shift'                         => 'nullable|string|max:255',

            'details'                       => 'required|array|min:1',
            'details.*.uuid'                => 'nullable|string',
            'details.*.product_uuid'        => 'required|exists:products,uuid',
            'details.*.time'                => 'nullable',
            'details.*.production_code'     => 'nullable|string|max:255',
            'details.*.best_before'         => 'nullable|date',
            'details.*.result'              => 'nullable|string|max:255',
            'details.*.corrective_action'   => 'nullable|string',
            'details.*.packaging'           => 'nullable|string',
            'details.*.photos.*'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'details.*.deleted_photos'      => 'nullable|array',
            'details.*.deleted_photos.*'    => 'nullable|string',
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
                    'production_code'    => $detail['production_code'] ?? null,
                    'best_before'        => $detail['best_before'] ?? null,
                    'result'             => $detail['result'] ?? null,
                    'corrective_action'  => $detail['corrective_action'] ?? null,
                    'packaging'          => $detail['packaging'] ?? null,
                ];

                $detailUuid = $detail['uuid'] ?? null;
                $detailRecord = $detailUuid
                    ? $report->details()->where('uuid', $detailUuid)->first()
                    : null;

                if ($detailRecord) {
                    // Row lama -> update, foto lama tetap aman
                    $detailRecord->update($fields);
                } else {
                    // Row baru ditambahkan di form edit -> create
                    $detailRecord = $report->details()->create($fields);
                }

                $submittedUuids[] = $detailRecord->uuid;

                // Hapus foto lama yang ditandai user untuk dihapus
                if (!empty($detail['deleted_photos'])) {
                    $photosToDelete = DetailStartupLabelPhoto::where('detail_uuid', $detailRecord->uuid)
                        ->whereIn('uuid', $detail['deleted_photos'])
                        ->get();

                    foreach ($photosToDelete as $photo) {
                        Storage::disk('public')->delete($photo->file_path);
                        $photo->delete();
                    }
                }

                // Simpan foto baru yang diupload
                if (!empty($detailsFiles[$i]['photos'])) {
                    foreach ($detailsFiles[$i]['photos'] as $photo) {
                        if ($photo && $photo->isValid()) {
                            $path = $photo->store('startup_label_photos', 'public');

                            DetailStartupLabelPhoto::create([
                                'uuid'        => Str::uuid(),
                                'detail_uuid' => $detailRecord->uuid,
                                'file_path'   => $path,
                            ]);
                        }
                    }
                }
            }

            // Row yang dihapus user di form (tidak ada di submittedUuids) -> hapus beneran
            $oldDetails = $report->details()->whereNotIn('uuid', $submittedUuids)->get();
            foreach ($oldDetails as $old) {
                foreach ($old->photos as $photo) {
                    Storage::disk('public')->delete($photo->file_path);
                }
                $old->delete(); // foto ikut kehapus via FK cascade
            }
        });

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $report = ReportStartupLabel::with('details.photos')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Hapus semua file foto fisik sebelum record-nya kehapus
        foreach ($report->details as $detail) {
            foreach ($detail->photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
            }
        }

        $report->delete(); // detail & photo record ikut terhapus karena FK cascade

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Report startup label berhasil dihapus.');
    }

    /**
     * Export laporan ke PDF.
     */
    public function exportPdf($uuid)
    {
        $report = ReportStartupLabel::with(['area', 'details.product', 'details.photos'])
            ->where('uuid', $uuid)
            ->firstOrFail();
 
        // Generate QR untuk created_by
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);
 
        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);
 
        // Generate QR untuk approved_by
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . ($report->approved_at ? \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i') : '-')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);
 
        $pdf = Pdf::loadView('report_startup_labels.pdf', [
            'report'     => $report,
            'createdQr'  => $createdQrBase64,
            'knownQr'    => $knownQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('a4', 'portrait');
 
        return $pdf->stream('laporan_startup_label_' . $report->date->format('Ymd') . '.pdf');
    }

    public function known($id)
    {
        $report = ReportStartupLabel::findOrFail($id);
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
        $report = ReportStartupLabel::findOrFail($id);
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
            $dateFrom = Carbon::createFromFormat('Y-m', $request->month)
                ->startOfMonth();

            $dateTo = $dateFrom->copy()->endOfMonth();

            $periodLabel = $dateFrom->translatedFormat('F Y');
        } else {
            $dateFrom = Carbon::parse($request->date_from)
                ->startOfDay();

            $dateTo = Carbon::parse($request->date_to)
                ->endOfDay();

            $periodLabel = $dateFrom->format('d/m/Y')
                . ' - '
                . $dateTo->format('d/m/Y');
        }

        $reports = ReportStartupLabel::with([
                'area',
                'details.product'
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString()
            ])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'Startup_Label_'
            . $dateFrom->format('Ymd')
            . '_'
            . $dateTo->format('Ymd')
            . '.xlsx';

        return Excel::download(
            new StartupLabelExport($reports, $periodLabel),
            $filename
        );
    }
}