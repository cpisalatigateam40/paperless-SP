<?php

namespace App\Http\Controllers;

use App\Models\ReportLabSample;
use App\Models\Area;
use App\Models\Product;
use App\Models\DetailLabSample;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\LabSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportLabSampleController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportLabSample::with(['area', 'details.product'])
            ->latest();

        // 🔍 GLOBAL SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('storage', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('accepted_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL SAMPLE
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('production_code', 'like', "%{$search}%")
                    ->orWhere('best_before', 'like', "%{$search}%")
                    ->orWhere('quantity', 'like', "%{$search}%")
                    ->orWhere('gramase', 'like', "%{$search}%")
                    ->orWhere('sample_type', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
                });

                // 🔹 PRODUCT
                $q->orWhereHas('details.product', function ($p) use ($search) {
                    $p->where('product_name', 'like', "%{$search}%")
                    ->orWhere('production_code', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('report_lab_samples.index', compact('reports'));
    }

    // Form create
    public function create()
    {
        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        return view('report_lab_samples.create', compact('areas', 'products'));
    }

    // Store data
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

            $report = ReportLabSample::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
                'storage' => implode(', ', $request->storage ?? []),
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailLabSample::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'quantity' => $detail['quantity'],
                    'notes' => $detail['notes'],
                    'gramase' => $detail['gramase'],
                    'sample_type' => $detail['sample_type'],
                    'unit' => $detail['unit'],
                ]);
            }
        });

        return redirect()->route('report_lab_samples.index')->with('success', 'Data berhasil disimpan');
    }

    // Delete
    public function destroy($id)
    {
        $report = ReportLabSample::findOrFail($id);
        $report->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportLabSample::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        return view('report_lab_samples.add_detail', compact('report', 'products'));
    }

    // Simpan detail baru
    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'production_code' => 'required',
            'best_before' => 'required|date',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $report = ReportLabSample::where('uuid', $report_uuid)->firstOrFail();

        DetailLabSample::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'best_before' => $request->best_before,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'gramase' => $request->gramase,
            'sample_type' => $request->sample_type,
            'unit' => $request->unit,
        ]);

        return redirect()->route('report_lab_samples.index')->with('success', 'Detail berhasil ditambahkan');
    }

    public function approve($id)
    {
        $report = ReportLabSample::findOrFail($id);
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
        $report = ReportLabSample::findOrFail($id);
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
        $report = ReportLabSample::where('uuid', $uuid)
            ->with('details.product', 'area')
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

        $pdf = Pdf::loadView('report_lab_samples.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('ReportLabSample-' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportLabSample::with('details.product')->where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_lab_samples.edit', compact('report', 'areas', 'products'));
    }

    public function update(Request $request, $uuid)
    {
        DB::transaction(function () use ($request, $uuid) {
            $report = ReportLabSample::where('uuid', $uuid)->firstOrFail();

            // Update header
            $report->update([
                'date' => $request->date,
                'shift' => $request->shift,
                'storage' => implode(', ', $request->storage ?? []),
            ]);

            // Hapus detail lama
            DetailLabSample::where('report_uuid', $report->uuid)->delete();

            // Simpan ulang detail
            foreach ($request->details as $detail) {
                DetailLabSample::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'quantity' => $detail['quantity'],
                    'notes' => $detail['notes'],
                    'gramase' => $detail['gramase'],
                    'sample_type' => $detail['sample_type'],
                    'unit' => $detail['unit'],
                ]);
            }
        });

        return redirect()->route('report_lab_samples.index')->with('success', 'Data berhasil diperbarui');
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
    
        $reports = ReportLabSample::with(['details.product'])
            ->when(auth()->user()->hasRole('QC Inspector'), fn($q) =>
                $q->where('area_uuid', auth()->user()->area_uuid)
            )
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Lab_Sample_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new LabSampleExport($reports, $periodLabel), $filename);
    }


}