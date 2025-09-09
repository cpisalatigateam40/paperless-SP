<?php

namespace App\Http\Controllers;

use App\Models\ReportRtgSteamer;
use App\Models\DetailRtgSteamer;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRtgSteamerController extends Controller
{
    public function index()
    {
        $reports = ReportRtgSteamer::with(['product', 'area'])->latest()->paginate(10);
        return view('report_rtg_steamers.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();
        return view('report_rtg_steamers.create', compact('products', 'areas'));
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
            $filePath = 'steamers/' . $fileName;

            if (!Storage::disk('public')->exists('steamers')) {
                Storage::disk('public')->makeDirectory('steamers');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }

    public function store(Request $request)
    {
        $report = ReportRtgSteamer::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'product_uuid' => $request->product_uuid,
            'created_by' => Auth::user()->name,
        ]);

        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {

                $qcParafPath = null;
                if (!empty($detail['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detail['production_paraf'])) {
                    $productionParafPath = $this->saveSignature($detail['production_paraf'], "production_{$index}");
                }

                DetailRtgSteamer::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'steamer' => $detail['steamer'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'trolley_count' => $detail['trolley_count'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'product_temp' => $detail['product_temp'] ?? null,
                    'time_minute' => $detail['time_minute'] ?? null,
                    'start_time' => $detail['start_time'] ?? null,
                    'end_time' => $detail['end_time'] ?? null,
                    'sensory_ripeness' => $detail['sensory_ripeness'] ?? null,
                    'sensory_taste' => $detail['sensory_taste'] ?? null,
                    'sensory_aroma' => $detail['sensory_aroma'] ?? null,
                    'sensory_texture' => $detail['sensory_texture'] ?? null,
                    'sensory_color' => $detail['sensory_color'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'production_paraf' => $productionParafPath,
                ]);
            }
        }

        return redirect()->route('report_rtg_steamers.index')
            ->with('success', 'Report berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportRtgSteamer::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_rtg_steamers.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function addDetail($reportUuid)
    {
        $report = ReportRtgSteamer::where('uuid', $reportUuid)->firstOrFail();
        return view('report_rtg_steamers.add_detail', compact('report'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportRtgSteamer::where('uuid', $reportUuid)->firstOrFail();

        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {

                $qcParafPath = null;
                if (!empty($detail['qc_paraf'])) {
                    $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
                }

                $productionParafPath = null;
                if (!empty($detail['production_paraf'])) {
                    $productionParafPath = $this->saveSignature($detail['production_paraf'], "production_{$index}");
                }

                DetailRtgSteamer::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'steamer' => $detail['steamer'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'trolley_count' => $detail['trolley_count'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'product_temp' => $detail['product_temp'] ?? null,
                    'time_minute' => $detail['time_minute'] ?? null,
                    'start_time' => $detail['start_time'] ?? null,
                    'end_time' => $detail['end_time'] ?? null,
                    'sensory_ripeness' => $detail['sensory_ripeness'] ?? null,
                    'sensory_taste' => $detail['sensory_taste'] ?? null,
                    'sensory_aroma' => $detail['sensory_aroma'] ?? null,
                    'sensory_texture' => $detail['sensory_texture'] ?? null,
                    'sensory_color' => $detail['sensory_color'] ?? null,
                    'qc_paraf' => $qcParafPath,
                    'production_paraf' => $productionParafPath,
                ]);
            }
        }

        return redirect()->route('report_rtg_steamers.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }

    public function known($id)
    {
        $report = ReportRtgSteamer::findOrFail($id);
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
        $report = ReportRtgSteamer::findOrFail($id);
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
        $report = ReportRtgSteamer::with('details', 'product', 'area')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_rtg_steamers.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape');

        return $pdf->stream("report-rtg-steamer-{$report->date}.pdf");
    }

}