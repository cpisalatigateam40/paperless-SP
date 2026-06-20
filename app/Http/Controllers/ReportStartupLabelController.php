<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\DetailStartupLabel;
use App\Models\Product;
use App\Models\ReportStartupLabel;
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

class ReportStartupLabelController extends Controller
{
    use HasBulkApproval;
    protected string $bulkModel = ReportStartupLabel::class;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReportStartupLabel::with([
            'area',
            'details.product'
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
            'details.*.packaging'  => 'nullable|string',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        // Simpan header laporan
        $report = ReportStartupLabel::create([
            'uuid'        => Str::uuid(),
            'area_uuid'   => Auth::user()->area_uuid,
            'date'        => $request->date,
            'shift'       => $shift,
            'created_by'  => Auth::user()->name,
            'known_by'    => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        // dd($request->details);

        // Simpan setiap baris detail produk
        foreach ($request->details as $detail) {
            DetailStartupLabel::create([
                'uuid'               => Str::uuid(),
                'report_uuid'        => $report->uuid,
                'product_uuid'       => $detail['product_uuid'],
                'time'               => $detail['time'] ?? null,
                'production_code'    => $detail['production_code'] ?? null,
                'best_before'        => $detail['best_before'] ?? null,
                'result'             => $detail['result'] ?? null,
                'corrective_action'  => $detail['corrective_action'] ?? null,
                'packaging'  => $detail['packaging'] ?? null,
            ]);
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
        $report = ReportStartupLabel::with('details')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_startup_labels.form', compact('report', 'areas', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $report = ReportStartupLabel::where('uuid', $uuid)->firstOrFail();
 
        $validated = $request->validate([
            'date'                         => 'required|date',
            'shift'                        => 'nullable|string|max:255',
 
            'details'                      => 'required|array|min:1',
            'details.*.product_uuid'       => 'required|exists:products,uuid',
            'details.*.time'               => 'nullable',
            'details.*.production_code'    => 'nullable|string|max:255',
            'details.*.best_before'        => 'nullable|date',
            'details.*.result'             => 'nullable|string|max:255',
            'details.*.corrective_action'  => 'nullable|string',
            'details.*.packaging'  => 'nullable|string',
        ]);
 
        DB::transaction(function () use ($validated, $report) {
            // Hanya update tanggal & shift. area_uuid, created_by, known_by,
            // approved_by, approved_at TIDAK disentuh karena form ini tidak
            // mengirim input untuk field-field tersebut — kalau ikut di-update
            // dengan null, nilainya akan hilang.
            $report->update([
                'date'  => $validated['date'],
                'shift' => $validated['shift'] ?? $report->shift,
            ]);
 
            // Cara simpel: hapus semua detail lama, lalu simpan ulang sesuai input
            $report->details()->delete();
 
            foreach ($validated['details'] as $detail) {
                $report->details()->create($detail);
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
        $report = ReportStartupLabel::where('uuid', $uuid)->firstOrFail();
        $report->delete(); // detail ikut terhapus karena FK cascade

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Report startup label berhasil dihapus.');
    }

    /**
     * Export laporan ke PDF.
     */
    public function exportPdf($uuid)
    {
        $report = ReportStartupLabel::with(['area', 'details.product'])
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