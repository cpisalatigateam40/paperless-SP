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
use App\Exports\RtgSteamerExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportRtgSteamerController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportRtgSteamer::with([
            'product',
            'area',
            'details',
        ])->latest();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 PRODUCT
                $q->orWhereHas('product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%");
                });

                // 🔹 DETAILS
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('steamer', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%")
                        ->orWhere('trolley_count', 'like', "%{$search}%")
                        ->orWhere('room_temp', 'like', "%{$search}%")
                        ->orWhere('product_temp', 'like', "%{$search}%")
                        ->orWhere('time_minute', 'like', "%{$search}%")
                        ->orWhere('start_time', 'like', "%{$search}%")
                        ->orWhere('end_time', 'like', "%{$search}%")

                        // 🔹 SENSORY
                        ->orWhere('sensory_ripeness', 'like', "%{$search}%")
                        ->orWhere('sensory_taste', 'like', "%{$search}%")
                        ->orWhere('sensory_aroma', 'like', "%{$search}%")
                        ->orWhere('sensory_texture', 'like', "%{$search}%")
                        ->orWhere('sensory_color', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $totalKetidaksesuaian = 0;

            foreach ($report->details as $detail) {
                foreach ([
                    'sensory_ripeness',
                    'sensory_taste',
                    'sensory_aroma',
                    'sensory_texture',
                    'sensory_color'
                ] as $field) {
                    if (isset($detail->$field) && $detail->$field === 'Tidak OK') {
                        $totalKetidaksesuaian++;
                    }
                }
            }

            $report->ketidaksesuaian = $totalKetidaksesuaian;
            return $report;
        });

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
        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        $report = ReportRtgSteamer::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $shift,
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

    public function edit($uuid)
    {
        $report = ReportRtgSteamer::with('details')->where('uuid', $uuid)->firstOrFail();
        $products = Product::all();
        $areas = Area::all();

        return view('report_rtg_steamers.edit', compact('report', 'products', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportRtgSteamer::where('uuid', $uuid)->firstOrFail();

        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'product_uuid' => $request->product_uuid,
        ]);

        // Hapus detail lama dan insert ulang (atau bisa diupdate satu-satu)
        DetailRtgSteamer::where('report_uuid', $report->uuid)->delete();

        if ($request->has('details')) {
            foreach ($request->details as $index => $detail) {
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
                ]);
            }
        }

        return redirect()->route('report_rtg_steamers.index')
            ->with('success', 'Report berhasil diperbarui.');
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
    
        $reports = ReportRtgSteamer::with(['product', 'details'])
            ->when(auth()->user()->hasRole('QC Inspector'), fn($q) =>
                $q->where('area_uuid', auth()->user()->area_uuid)
            )
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'RTG_Steamer_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new RtgSteamerExport($reports, $periodLabel), $filename);
    }


}