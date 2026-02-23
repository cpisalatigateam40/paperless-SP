<?php

namespace App\Http\Controllers;

use App\Models\ReportMdProduct;
use App\Models\DetailMdProduct;
use App\Models\PositionMdProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\BestBeforeService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MdProductImport;
use App\Exports\MdProductTemplateExport;

class ReportMdProductController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportMdProduct::with([
            'area',
            'details.product',
            'details.positions'
        ])->latest();

        // 🔍 GLOBAL SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL MD PRODUCT
                $q->orWhereHas('details', function ($d) use ($search) {

                    $d->where('production_code', 'like', "%{$search}%")
                    ->orWhere('program_number', 'like', "%{$search}%")
                    ->orWhere('process_type', 'like', "%{$search}%")
                    ->orWhere('corrective_action', 'like', "%{$search}%")
                    ->orWhere('gramase', 'like', "%{$search}%")
                    ->orWhere('best_before', 'like', "%{$search}%")
                    ->orWhere('time', 'like', "%{$search}%");

                    // 🔹 VERIFICATION (boolean)
                    if (strtolower($search) === 'ok') {
                        $d->orWhere('verification', true);
                    }

                    if (strtolower($search) === 'tidak ok') {
                        $d->orWhere('verification', false);
                    }

                    // 🔹 PRODUCT
                    $d->orWhereHas('product', function ($p) use ($search) {
                        $p->where('product_name', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%");
                    });

                    // 🔹 POSITIONS (PALING DALAM)
                    $d->orWhereHas('positions', function ($pos) use ($search) {
                        $pos->where('specimen', 'like', "%{$search}%")
                            ->orWhere('position', 'like', "%{$search}%");

                        if (strtolower($search) === 'ok') {
                            $pos->orWhere('status', true);
                        }

                        if (strtolower($search) === 'tidak ok') {
                            $pos->orWhere('status', false);
                        }
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        foreach ($reports as $report) {
            $totalNonConform = 0;

            foreach ($report->details as $detail) {

                // 1️⃣ Verifikasi setelah perbaikan
                if ($detail->verification === false) {
                    $totalNonConform++;
                    continue;
                }

                // 2️⃣ Posisi MD (status = false)
                if ($detail->positions->contains('status', false)) {
                    $totalNonConform++;
                }
            }

            $report->ketidaksesuaian = $totalNonConform;
        }

        return view('report_md_products.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        return view('report_md_products.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Validasi minimal header, sesuaikan sesuai kebutuhan
        $request->validate([
            'date' => 'required|date',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        // Simpan header report
        $report = ReportMdProduct::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $shift,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail jika ada
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
                    'verification' => isset($detail['verification']) ? (bool) $detail['verification'] : false,
                    'process_type' => $detail['process_type'] ?? null,
                ]);


                // Simpan posisi jika ada
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
        $products = \App\Models\Product::all();
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
                    'verification' => isset($detail['verification']) ? (bool) $detail['verification'] : false,
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
            ->with(['details.positions', 'details.product'])
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

        $pdf = PDF::loadView('report_md_products.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);
        return $pdf->stream('Report-Metal-Detector-' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportMdProduct::with(['details.positions', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $products = Product::all();
        return view('report_md_products.edit', compact('report', 'products'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportMdProduct::where('uuid', $uuid)->firstOrFail();

        // Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
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
                    'verification' => isset($detail['verification']) ? (bool) $detail['verification'] : false,
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


}