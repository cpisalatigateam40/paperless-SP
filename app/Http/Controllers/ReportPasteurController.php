<?php

namespace App\Http\Controllers;

use App\Models\ReportPasteur;
use App\Models\DetailPasteur;
use App\Models\StepPasteur;
use App\Models\StandardStep;
use App\Models\DrainageStep;
use App\Models\FinishStep;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPasteurController extends Controller
{
    public function index()
    {
        $reports = ReportPasteur::with([
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
            'area'
        ])->paginate(10);

        return view('report_pasteurs.index', compact('reports'));
    }

    /**
     * Form create report baru
     */
    public function create()
    {
        $areas = Area::all();
        $products = Product::all();

        return view('report_pasteurs.create', compact('areas', 'products'));
    }

    private function saveSignature($base64Image, $prefix)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, dll

            $image = base64_decode($image);
            if ($image === false) {
                return null;
            }

            $fileName = $prefix . '_' . time() . '.' . $type;
            $filePath = 'Pasteurisasi/' . $fileName;

            if (!Storage::disk('public')->exists('Pasteurisasi')) {
                Storage::disk('public')->makeDirectory('Pasteurisasi');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }

    /**
     * Simpan report baru
     */
    public function store(Request $request)
    {
        // 1. Simpan Report
        $report = ReportPasteur::create([
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'problem' => $request->problem,
            'corrective_action' => $request->corrective_action,
        ]);

        // 2. Simpan Detail
        if ($request->has('details')) {
            foreach ($request->details as $index => $detailData) {

                $qcParafPath = null;
                if (!empty($detailData['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detailData['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detailData['production_paraf'])) {
                    $productionParafPath = $this->saveSignature($detailData['production_paraf'], "production_{$index}");
                }

                $detail = DetailPasteur::create([
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'] ?? null,
                    'program_number' => $detailData['program_number'] ?? null,
                    'product_code' => $detailData['product_code'] ?? null,
                    'for_packaging_gr' => $detailData['for_packaging_gr'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'product_temp' => $detailData['product_temp'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'production_paraf' => $productionParafPath,
                ]);

                // 3. Simpan Step
                if (isset($detailData['steps'])) {
                    foreach ($detailData['steps'] as $stepData) {
                        $step = StepPasteur::create([
                            'detail_uuid' => $detail->uuid,
                            'step_name' => $stepData['step_name'] ?? null,
                            'step_order' => $stepData['step_order'] ?? null,
                            'step_type' => $stepData['step_type'] ?? null,
                        ]);

                        // 4. Simpan ke tabel sesuai tipe
                        if (($stepData['step_type'] ?? '') === 'standard') {
                            StandardStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'drainage') {
                            DrainageStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'finish') {
                            FinishStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }
                    }
                }
            }
        }

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Report Pasteurisasi berhasil ditambahkan lengkap dengan detail & step');
    }



    /**
     * Hapus report
     */
    public function destroy($uuid)
    {
        $report = ReportPasteur::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Report berhasil dihapus');
    }

    public function addDetail($reportUuid)
    {
        $report = ReportPasteur::where('uuid', $reportUuid)->firstOrFail();
        $products = Product::all();

        return view('report_pasteurs.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportPasteur::where('uuid', $reportUuid)->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $index => $detailData) {

                $qcParafPath = null;
                if (!empty($detailData['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detailData['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detailData['production_paraf'])) {
                    $productionParafPath = $this->saveSignature($detailData['production_paraf'], "production_{$index}");
                }

                $detail = DetailPasteur::create([
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'] ?? null,
                    'program_number' => $detailData['program_number'] ?? null,
                    'product_code' => $detailData['product_code'] ?? null,
                    'for_packaging_gr' => $detailData['for_packaging_gr'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'product_temp' => $detailData['product_temp'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'production_paraf' => $productionParafPath,
                ]);

                // simpan step
                if (isset($detailData['steps'])) {
                    foreach ($detailData['steps'] as $stepData) {
                        $step = StepPasteur::create([
                            'detail_uuid' => $detail->uuid,
                            'step_name' => $stepData['step_name'] ?? null,
                            'step_order' => $stepData['step_order'] ?? null,
                            'step_type' => $stepData['step_type'] ?? null,
                        ]);

                        if (($stepData['step_type'] ?? '') === 'standard') {
                            StandardStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'drainage') {
                            DrainageStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }

                        if (($stepData['step_type'] ?? '') === 'finish') {
                            FinishStep::create(array_merge($stepData['data'] ?? [], [
                                'step_uuid' => $step->uuid,
                            ]));
                        }
                    }
                }
            }
        }

        return redirect()->route('report_pasteurs.index')
            ->with('success', 'Detail berhasil ditambahkan ke laporan pasteurisasi');
    }

    public function known($id)
    {
        $report = ReportPasteur::findOrFail($id);
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
        $report = ReportPasteur::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function exportPdf($uuid)
    {
        // Ambil report beserta relasi
        $report = ReportPasteur::with([
            'details.steps.standardStep',
            'details.steps.drainageStep',
            'details.steps.finishStep',
            'area',
            'details.product'
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


        // Kirim data ke view PDF
        $pdf = Pdf::loadView('report_pasteurs.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'landscape');

        // Download PDF
        return $pdf->stream("Report_Pasteur_{$report->uuid}.pdf");
    }
}