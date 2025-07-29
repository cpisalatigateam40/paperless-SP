<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportPackagingVerif;
use App\Models\DetailPackagingVerif;
use App\Models\ChecklistPackagingDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPackagingVerifController extends Controller
{
    public function index()
    {
        $reports = ReportPackagingVerif::with('details.checklist')->get();
        return view('report_packaging_verifs.index', compact('reports'));
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        $areas = \App\Models\Area::all();
        $sections = \App\Models\Section::all();

        return view('report_packaging_verifs.create', compact('products', 'areas', 'sections'));
    }

    public function store(Request $request)
    {
        $report = ReportPackagingVerif::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $detail) {
            $detailModel = DetailPackagingVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'production_code' => $detail['production_code'],
                'expired_date' => $detail['expired_date'],
                'qc_verif' => isset($detail['qc_verif']) ? Auth::user()->name : null,
                'kr_verif' => isset($detail['kr_verif']) ? Auth::user()->name : null,
            ]);

            $checklistData = [
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'standard_weight' => $detail['checklist']['standard_weight'],
            ];

            $fields = [
                'in_cutting_manual',
                'in_cutting_machine',
                'packaging_thermoformer',
                'packaging_manual',
                'sealing_condition',
                'sealing_vacuum',
                'content_per_pack',
                'actual_weight'
            ];

            foreach ($fields as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            ChecklistPackagingDetail::create($checklistData);
        }

        return redirect()->route('report_packaging_verifs.index')->with('success', 'Laporan berhasil disimpan.');
    }


    public function destroy($uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_packaging_verifs.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();
        $products = \App\Models\Product::all();

        return view('report_packaging_verifs.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details as $detail) {
            $detailModel = DetailPackagingVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'production_code' => $detail['production_code'],
                'expired_date' => $detail['expired_date'],
                'qc_verif' => isset($detail['qc_verif']) ? Auth::user()->name : null,
                'kr_verif' => isset($detail['kr_verif']) ? Auth::user()->name : null,
            ]);

            $checklistData = [
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'standard_weight' => $detail['checklist']['standard_weight'],
            ];

            $fields = [
                'in_cutting_manual',
                'in_cutting_machine',
                'packaging_thermoformer',
                'packaging_manual',
                'sealing_condition',
                'sealing_vacuum',
                'content_per_pack',
                'actual_weight'
            ];

            foreach ($fields as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            ChecklistPackagingDetail::create($checklistData);
        }

        return redirect()->route('report_packaging_verifs.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportPackagingVerif::findOrFail($id);
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
        $report = ReportPackagingVerif::findOrFail($id);
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
        $report = ReportPackagingVerif::with('details.checklist', 'details.product')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_packaging_verifs.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape');

        return $pdf->stream('report-packaging-' . $report->date . '.pdf');
    }

}