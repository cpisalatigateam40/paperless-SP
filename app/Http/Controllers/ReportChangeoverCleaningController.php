<?php

namespace App\Http\Controllers;

use App\Models\DetailChangeoverCleaning;
use App\Models\MasterChecklistItem;
use App\Models\Product;
use App\Models\ReportChangeoverCleaning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasBulkApproval;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChangeoverCleaningExport;

class ReportChangeoverCleaningController extends Controller
{
    use HasBulkApproval;

    protected string $bulkModel = ReportChangeoverCleaning::class;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReportChangeoverCleaning::with([
            'area',
            'details.product',
            'details.item',
        ])->latest('date');

        // SEARCH ALL KOLOM
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // AREA
                $q->orWhereHas('area', function ($aq) use ($search) {
                    $aq->where('name', 'like', "%{$search}%");
                });

                // DETAIL
                $q->orWhereHas('details', function ($dq) use ($search) {

                    $dq->where('time', 'like', "%{$search}%")
                        ->orWhere('result', 'like', "%{$search}%")
                        ->orWhere('explanation', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")

                        // NAMA PRODUK
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('product_name', 'like', "%{$search}%");
                        })

                        // ITEM CHECKLIST
                        ->orWhereHas('item', function ($iq) use ($search) {
                            $iq->where('name', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%");
                        });
                });
            });
        }

        $reports = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'report_changeover_cleanings.index',
            compact('reports')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = MasterChecklistItem::where('area_uuid', auth()->user()->area_uuid)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        return view('report_changeover_cleanings.form', compact('items', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'                                   => 'required|date',
            'batches'                                => 'required|array|min:1',
            'batches.*.product_uuid'                 => 'required|exists:products,uuid',
            'batches.*.time'                         => 'nullable',
            'batches.*.items'                        => 'required|array|min:1',
            'batches.*.items.*.result'               => 'nullable|string|max:255',
            'batches.*.items.*.explanation'          => 'nullable|string',
            'batches.*.items.*.notes'                => 'nullable|string',
            'batches.*.items.*.corrective_action'    => 'nullable|string',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

        // Simpan header laporan
        $report = ReportChangeoverCleaning::create([
            'uuid'        => Str::uuid(),
            'area_uuid'   => Auth::user()->area_uuid,
            'date'        => $request->date,
            'shift'       => $shift,
            'created_by'  => Auth::user()->name,
            'known_by'    => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        // Setiap batch = 1 produk yang dicek ke semua item di dalamnya
        foreach ($request->batches as $batch) {
            foreach ($batch['items'] as $itemUuid => $itemResult) {
                DetailChangeoverCleaning::create([
                    'uuid'               => Str::uuid(),
                    'report_uuid'        => $report->uuid,
                    'item_uuid'          => $itemUuid,
                    'product_uuid'       => $batch['product_uuid'],
                    'time'               => $batch['time'] ?? null,
                    'result'             => $itemResult['result'] ?? null,
                    'explanation'        => $itemResult['explanation'] ?? null,
                    'notes'              => $itemResult['notes'] ?? null,
                    'corrective_action'  => $itemResult['corrective_action'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $report = ReportChangeoverCleaning::with(['area', 'details.item', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('report_changeover_cleanings.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $report = ReportChangeoverCleaning::with('details')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $items = MasterChecklistItem::orderBy('category')->orderBy('name')->get();
        $products = Product::selectRaw(
                'MIN(uuid) as uuid, product_name'
            )
            ->groupBy('product_name')
            ->get();

        return view('report_changeover_cleanings.form', compact('report', 'items', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $report = ReportChangeoverCleaning::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'date'                                   => 'required|date',
            'shift'                                  => 'nullable|string|max:255',

            'batches'                                => 'required|array|min:1',
            'batches.*.product_uuid'                 => 'required|exists:products,uuid',
            'batches.*.time'                         => 'nullable',
            'batches.*.items'                        => 'required|array|min:1',
            'batches.*.items.*.result'               => 'nullable|string|max:255',
            'batches.*.items.*.explanation'          => 'nullable|string',
            'batches.*.items.*.notes'                => 'nullable|string',
            'batches.*.items.*.corrective_action'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $report) {
            // Hanya update tanggal & shift. area_uuid, created_by, known_by,
            // approved_by TIDAK disentuh karena form ini tidak mengirim input
            // untuk field-field tersebut.
            $report->update([
                'date'  => $validated['date'],
                'shift' => $validated['shift'] ?? $report->shift,
            ]);

            // Cara simpel: hapus semua detail lama, lalu simpan ulang sesuai input
            $report->details()->delete();

            foreach ($validated['batches'] as $batch) {
                foreach ($batch['items'] as $itemUuid => $itemResult) {
                    $report->details()->create([
                        'item_uuid'          => $itemUuid,
                        'product_uuid'       => $batch['product_uuid'],
                        'time'               => $batch['time'] ?? null,
                        'result'             => $itemResult['result'] ?? null,
                        'explanation'        => $itemResult['explanation'] ?? null,
                        'notes'              => $itemResult['notes'] ?? null,
                        'corrective_action'  => $itemResult['corrective_action'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $report = ReportChangeoverCleaning::where('uuid', $uuid)->firstOrFail();
        $report->delete(); // detail ikut terhapus karena FK cascade

        return redirect()
            ->route('report_changeover_cleanings.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportChangeoverCleaning::with(['area', 'details.item', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();
 
        // Generate QR untuk created_by
        $createdInfo = "Dilaporkan oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
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
            ? "Diperiksa oleh: {$report->approved_by}"
            : "Belum diperiksa";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);
 
        $pdf = Pdf::loadView('report_changeover_cleanings.pdf', [
            'report'     => $report,
            'createdQr'  => $createdQrBase64,
            'knownQr'    => $knownQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('F4', 'landscape');
 
        return $pdf->stream('laporan_kebersihan_pergantian_produk_' . $report->date->format('Ymd') . '.pdf');
    }

    /**
     * Known by
     */
    public function known($id)
    {
        $report = ReportChangeoverCleaning::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah diketahui.'
                );
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil diketahui.'
            );
    }

    /**
     * Approve
     */
    public function approve($id)
    {
        $report = ReportChangeoverCleaning::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah disetujui.'
                );
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil disetujui.'
            );
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

            $dateFrom = Carbon::createFromFormat(
                'Y-m',
                $request->month
            )->startOfMonth();

            $dateTo = $dateFrom->copy()->endOfMonth();

            $periodLabel = $dateFrom->translatedFormat('F Y');

        } else {

            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $dateTo   = Carbon::parse($request->date_to)->endOfDay();

            $periodLabel =
                $dateFrom->format('d/m/Y')
                . ' - '
                . $dateTo->format('d/m/Y');
        }

        $reports = ReportChangeoverCleaning::with([
                'details.item',
                'details.product',
                'area'
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween(
                'date',
                [
                    $dateFrom->toDateString(),
                    $dateTo->toDateString()
                ]
            )
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        return Excel::download(
            new ChangeoverCleaningExport(
                $reports,
                $periodLabel
            ),
            'Changeover_Cleaning.xlsx'
        );
    }
}