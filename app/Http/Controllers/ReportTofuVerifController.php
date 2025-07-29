<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportTofuVerif;
use App\Models\TofuProductInfo;
use App\Models\TofuWeightVerif;
use App\Models\TofuDefectVerif;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportTofuVerifController extends Controller
{
    public function index()
    {
        $reports = ReportTofuVerif::latest()->with(['productInfos', 'weightVerifs', 'defectVerifs'])->get();

        return view('report_tofu_verifs.index', compact('reports'));
    }

    public function create()
    {
        return view('report_tofu_verifs.create');
    }

    public function store(Request $request)
    {
        $report = ReportTofuVerif::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->products ?? [] as $product) {
            // Simpan product info
            $productInfo = TofuProductInfo::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'production_code' => $product['production_code'] ?? null,
                'expired_date' => $product['expired_date'] ?? null,
                'sample_amount' => $product['sample_amount'] ?? null,
            ]);

            // Simpan verifikasi berat
            foreach ($product['weight_verifs'] ?? [] as $category => $data) {
                TofuWeightVerif::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_info_uuid' => $productInfo->uuid,
                    'weight_category' => $category,
                    'turus' => $data['turus'] ?? null,
                    'total' => $data['total'] ?? null,
                    'percentage' => $data['percentage'] ?? null,
                ]);
            }

            // Simpan verifikasi defect
            foreach ($product['defect_verifs'] ?? [] as $type => $data) {
                TofuDefectVerif::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_info_uuid' => $productInfo->uuid,
                    'defect_type' => $type,
                    'turus' => $data['turus'] ?? null,
                    'total' => $data['total'] ?? null,
                    'percentage' => $data['percentage'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_tofu_verifs.index')->with('success', 'Report successfully saved.');
    }

    public function destroy($uuid)
    {
        $report = ReportTofuVerif::where('uuid', $uuid)->firstOrFail();

        $report->productInfos()->delete();
        $report->weightVerifs()->delete();
        $report->defectVerifs()->delete();
        $report->delete();

        return back()->with('success', 'Report deleted.');
    }

    public function edit($uuid)
    {
        $report = ReportTofuVerif::with([
            'productInfos',
            'weightVerifs',
            'defectVerifs'
        ])->where('uuid', $uuid)->firstOrFail();

        return view('report_tofu_verifs.edit', compact('report'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportTofuVerif::where('uuid', $uuid)->firstOrFail();

        // Hapus semua verifikasi & produk lama
        TofuWeightVerif::where('report_uuid', $report->uuid)->delete();
        TofuDefectVerif::where('report_uuid', $report->uuid)->delete();
        TofuProductInfo::where('report_uuid', $report->uuid)->delete();

        // Simpan ulang semua produk dan verifikasinya
        foreach ($request->products ?? [] as $product) {
            // Simpan product info
            $productInfo = TofuProductInfo::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'production_code' => $product['production_code'] ?? null,
                'expired_date' => $product['expired_date'] ?? null,
                'sample_amount' => $product['sample_amount'] ?? null,
            ]);

            // Simpan verifikasi berat
            foreach ($product['weight_verifs'] ?? [] as $category => $data) {
                TofuWeightVerif::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_info_uuid' => $productInfo->uuid,
                    'weight_category' => $category,
                    'turus' => $data['turus'] ?? null,
                    'total' => $data['total'] ?? null,
                    'percentage' => $data['percentage'] ?? null,
                ]);
            }

            // Simpan verifikasi defect
            foreach ($product['defect_verifs'] ?? [] as $type => $data) {
                TofuDefectVerif::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_info_uuid' => $productInfo->uuid,
                    'defect_type' => $type,
                    'turus' => $data['turus'] ?? null,
                    'total' => $data['total'] ?? null,
                    'percentage' => $data['percentage'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_tofu_verifs.index')->with('success', 'Report updated.');
    }

    public function approve($id)
    {
        $report = ReportTofuVerif::findOrFail($id);
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
        $report = ReportTofuVerif::findOrFail($id);
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
        $report = ReportTofuVerif::with(['productInfos', 'weightVerifs', 'defectVerifs'])->where('uuid', $uuid)->firstOrFail();

        // Group weight & defect verifs per produk
        $weightGroups = $report->weightVerifs->chunk(3);  // 3 kategori per produk
        $defectGroups = $report->defectVerifs->chunk(6);  // 6 jenis defect per produk

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

        $pdf = Pdf::loadView('report_tofu_verifs.export', [
            'report' => $report,
            'weightGroups' => $weightGroups,
            'defectGroups' => $defectGroups,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan Verifikasi Tofu - ' . $report->date . '.pdf');
    }

}