<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportProdLossVacum;
use App\Models\DetailProdLossVacum;
use App\Models\LossVacumDefect;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportProdLossVacumController extends Controller
{
    public function index()
    {
        $reports = ReportProdLossVacum::with('details.defects', 'details.product')->latest()->get();
        return view('report_prod_loss_vacums.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        return view('report_prod_loss_vacums.create', compact('products'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $report = ReportProdLossVacum::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detailData) {
                $detail = DetailProdLossVacum::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'],
                    'vacum_machine' => $detailData['vacum_machine'],
                    'sample_amount' => $detailData['sample_amount'],
                ]);

                foreach ($detailData['defects'] as $defectData) {
                    LossVacumDefect::create([
                        'uuid' => Str::uuid(),
                        'detail_uuid' => $detail->uuid,
                        'category' => $defectData['category'],
                        'pack_amount' => $defectData['pack_amount'],
                        'percentage' => $defectData['percentage'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('report_prod_loss_vacums.index')->with('success', 'Laporan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($uuid)
    {
        $report = ReportProdLossVacum::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_prod_loss_vacums.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportProdLossVacum::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();

        return view('report_prod_loss_vacums.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportProdLossVacum::where('uuid', $uuid)->firstOrFail();

        DB::beginTransaction();

        try {
            foreach ($request->details as $detailData) {
                $detail = DetailProdLossVacum::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'],
                    'vacum_machine' => $detailData['vacum_machine'],
                    'sample_amount' => $detailData['sample_amount'],
                ]);

                foreach ($detailData['defects'] as $defectData) {
                    LossVacumDefect::create([
                        'uuid' => Str::uuid(),
                        'detail_uuid' => $detail->uuid,
                        'category' => $defectData['category'],
                        'pack_amount' => $defectData['pack_amount'],
                        'percentage' => $defectData['percentage'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('report_prod_loss_vacums.index')->with('success', 'Detail produk berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $report = ReportProdLossVacum::findOrFail($id);
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
        $report = ReportProdLossVacum::findOrFail($id);
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
        $report = ReportProdLossVacum::with([
            'details.defects',
            'details.product',
            'area'
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

        $pdf = PDF::loadView('report_prod_loss_vacums.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_Loss_Vacuum_' . $report->date . '.pdf');
    }
}