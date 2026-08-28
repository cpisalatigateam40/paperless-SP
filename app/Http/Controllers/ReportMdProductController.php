<?php

namespace App\Http\Controllers;

use App\Models\ReportMdProduct;
use App\Models\DetailMdProduct;
use App\Models\PositionMdProduct;
use App\Models\Area;
use App\Models\Product;
use App\Models\MetalDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\BestBeforeService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MdProductImport;
use App\Exports\MdProductTemplateExport;
use App\Exports\MdProductExport;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;

class ReportMdProductController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;
    protected string $bulkModel = ReportMdProduct::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportMdProduct::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_md_products.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['details.positions', 'details.product'];
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
        return 'laporan_md_products';
    }

    public function index(Request $request)
    {
        $query = ReportMdProduct::with([
            'area',
            'metalDetector',
            'details.product',
            'details.positions'
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
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                $q->orWhereHas('metalDetector', function ($m) use ($search) {
                    $m->where('merk', 'like', "%{$search}%")
                        ->orWhere('type_model', 'like', "%{$search}%")
                        ->orWhere('no_series', 'like', "%{$search}%");
                });

                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('production_code', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('verification', 'like', "%{$search}%")
                        ->orWhere('gramase', 'like', "%{$search}%")
                        ->orWhere('best_before', 'like', "%{$search}%")
                        ->orWhere('time', 'like', "%{$search}%");

                    $d->orWhereHas('product', function ($p) use ($search) {
                        $p->where('product_name', 'like', "%{$search}%")
                            ->orWhere('production_code', 'like', "%{$search}%");
                    });

                    $d->orWhereHas('positions', function ($pos) use ($search) {
                        $pos->where('specimen', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");
                        if (in_array(strtolower($search), ['ok'])) {
                            $pos->orWhere('status', true);
                        }
                        if (in_array(strtolower($search), ['tidak ok', 'ng'])) {
                            $pos->orWhere('status', false);
                        }
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

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN — berdasarkan status specimen saja
        foreach ($reports as $report) {
            $totalNonConform = 0;
            foreach ($report->details as $detail) {
                if ($detail->positions->contains('status', false)) {
                    $totalNonConform++;
                }
            }
            $report->ketidaksesuaian = $totalNonConform;
        }

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_md_products.index', compact('reports', 'areas'));
    }

    public function create()
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        $metalDetectors = MetalDetector::where('is_active', true)->get();

        return view('report_md_products.create', compact('products', 'metalDetectors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'metal_detector_uuid' => 'required|exists:metal_detectors,uuid',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        $report = ReportMdProduct::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'metal_detector_uuid' => $request->metal_detector_uuid,
            'date' => $request->date,
            'shift' => $shift,
            'created_by' => Auth::user()->name,
            'notes' => $request->notes,
        ]);

        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $detailModel = DetailMdProduct::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'gramase' => $detail['gramase'] ?? null,
                    'best_before' => $detail['best_before'] ?? null,
                    'time' => $detail['time'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                    'status' => isset($detail['status']) ? (bool) $detail['status'] : true,
                ]);

                if (!empty($detail['positions'])) {
                    foreach ($detail['positions'] as $position) {
                        PositionMdProduct::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'specimen' => $position['specimen'] ?? null,
                            'position' => $position['position'] ?? null,
                            'status' => isset($position['status']) ? (bool) $position['status'] : false,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('report_md_products.index')
            ->with('success', 'Report berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_md_products.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        return view('report_md_products.add-detail', compact('report', 'products'));
    }
 
    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
 
        if ($request->details) {
            foreach ($request->details as $detail) {
                $detailModel = DetailMdProduct::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'gramase' => $detail['gramase'] ?? null,
                    'best_before' => $detail['best_before'] ?? null,
                    'time' => $detail['time'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                    'status' => isset($detail['status']) ? (bool) $detail['status'] : true,
                    'process_type' => $detail['process_type'] ?? null,
                ]);
 
                if (!empty($detail['positions'])) {
                    foreach ($detail['positions'] as $position) {
                        \App\Models\PositionMdProduct::create([
                            'uuid' => \Illuminate\Support\Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'specimen' => $position['specimen'] ?? null,
                            'position' => $position['position'] ?? null,
                            'status' => isset($position['status']) ? (bool) $position['status'] : false,
                        ]);
                    }
                }
            }
        }
 
        return redirect()->route('report_md_products.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportMdProduct::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function known($id)
    {
        $report = ReportMdProduct::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)
            ->with(['area', 'metalDetector', 'details.positions', 'details.product'])
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

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_md_products');

        $view = $report->metal_detector_uuid
            ? 'report_md_products.pdf'
            : 'report_md_products.export-pdf-legacy';

        $pdf = Pdf::loadView($view, [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'knownQr' => $knownQrBase64,
            'approvedQr' => $approvedQrBase64,
            'formNumber' => $formNumber,
        ]);

        return $pdf->stream('verifikasi-md-product-' . $report->uuid . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportMdProduct::with(['details.positions', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();
 
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name, MAX(shelf_life) as shelf_life, MAX(created_at) as created_at')
        ->groupBy('product_name')
        ->get();
        return view('report_md_products.edit', compact('report', 'products'));
    }
 
    public function update(Request $request, $uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();
 
        // Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'notes' => $request->notes,
        ]);
 
        // Hapus detail lama sebelum menulis ulang
        foreach ($report->details as $oldDetail) {
            $oldDetail->positions()->delete();
            $oldDetail->delete();
        }
 
        // Simpan detail baru dari form edit
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $detailModel = DetailMdProduct::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'gramase' => $detail['gramase'] ?? null,
                    'best_before' => $detail['best_before'] ?? null,
                    'time' => $detail['time'] ?? null,
                    'program_number' => $detail['program_number'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                    'status' => isset($detail['status']) ? (bool) $detail['status'] : true,
                    'process_type' => $detail['process_type'] ?? null,
                ]);
 
                // Simpan ulang posisi
                if (!empty($detail['positions'])) {
                    foreach ($detail['positions'] as $position) {
                        PositionMdProduct::create([
                            'uuid' => Str::uuid(),
                            'detail_uuid' => $detailModel->uuid,
                            'specimen' => $position['specimen'] ?? null,
                            'position' => $position['position'] ?? null,
                            'status' => isset($position['status']) ? (bool) $position['status'] : false,
                        ]);
                    }
                }
            }
        }
 
        return redirect()->route('report_md_products.index')
            ->with('success', 'Report berhasil diperbarui.');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new MdProductTemplateExport,
            'template-md-produk.xlsx'
        );
    }

    /* ================= IMPORT EXCEL ================= */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new MdProductImport, $request->file('file'));

        return redirect()
            ->route('report_md_products.index')
            ->with('success', 'Import MD Produk berhasil.');
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
    
        $reports = ReportMdProduct::with([
                'details.product',
                'details.positions',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'MD_Produk_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new MdProductExport($reports, $periodLabel), $filename);
    }


}