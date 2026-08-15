<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportFragileItem;
use App\Models\DetailFragileItem;
use App\Models\FragileItem;
use App\Models\DetailFragileItemManual;
use App\Models\Section;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\FragileItemExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;


class ReportFragileItemController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;
    protected string $bulkModel = ReportFragileItem::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportFragileItem::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_fragile_item.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['details.item'];
    }

    protected function getBulkExportExtraData($report): array
    {
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQr = QrCode::format('png')->size(150)->generate($createdInfo);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQr = QrCode::format('png')->size(150)->generate($approvedInfo);

        $knownInfo = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
        $knownQr = QrCode::format('png')->size(150)->generate($knownInfo);

        return [
            'createdQr'  => 'data:image/png;base64,' . base64_encode($createdQr),
            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQr),
            'knownQr'    => 'data:image/png;base64,' . base64_encode($knownQr),
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan_fragile_item';
    }

    public function index(Request $request)
    {
        $query = ReportFragileItem::with([
            'area',
            'details.item',
            'detailManuals.section'
        ]);

        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // 🔍 Tanggal
        $query->when($request->date, function ($q) use ($request) {
            $q->whereDate('date', $request->date);
        });

        // 🔍 Shift
        $query->when($request->shift, function ($q) use ($request) {
            $q->where('shift', $request->shift);
        });

        // 🔍 Global Search
        $query->when($request->search, function ($q) use ($request) {

            $search = $request->search;

            $q->where(function ($qq) use ($search) {

                // 🔹 Header report
                $qq->where('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%");

                // 🔹 Area
                $qq->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 Detail laporan
                $qq->orWhereHas('details', function ($d) use ($search) {

                    $d->where('notes', 'like', "%{$search}%")
                        ->orWhere('actual_quantity', 'like', "%{$search}%")

                        // 🔹 Master Fragile Item
                        ->orWhereHas('item', function ($i) use ($search) {
                            $i->where('item_name', 'like', "%{$search}%")
                                ->orWhere('section_name', 'like', "%{$search}%")
                                ->orWhere('owner', 'like', "%{$search}%");
                        });

                });

            });

        });

        // 📅 FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // 🔽 SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'relation' => 'details',
                'column' => 'production_code',
            ],
        ]);

        $reports = $query->paginate(10)
            ->withQueryString();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_fragile_item.index', compact('reports', 'areas'));
    }

    public function create()
    {
        $fragileItems = FragileItem::orderBy('section_name')->get();
        $sections = Section::orderBy('section_name')->get();

        return view('report_fragile_item.create', compact('fragileItems', 'sections'))->with('isEdit', false);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'manual_items.*.item_name' => 'nullable|string',
            'manual_items.*.quantity' => 'nullable|integer',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        $report = ReportFragileItem::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->items as $data) {
            DetailFragileItem::create([
                'uuid' => Str::uuid(),
                'report_fragile_item_uuid' => $report->uuid,
                'fragile_item_uuid' => $data['fragile_item_uuid'],
                'time_start' => $data['time_start'] ?? '0',
                'time_end' => $data['time_end'] ?? '0',
                'notes' => $data['notes'] ?? '0',
            ]);
        }

        foreach ($request->manual_items ?? [] as $manual) {
            if (empty($manual['item_name'])) {
                continue; // skip row kosong
            }

            DetailFragileItemManual::create([
                'uuid' => Str::uuid(),
                'report_fragile_item_uuid' => $report->uuid,
                'section_uuid' => $manual['section_uuid'] ?? null,
                'sub_area' => $manual['sub_area'] ?? null,
                'item_name' => $manual['item_name'],
                'quantity' => $manual['quantity'] ?? 0,
                'condition' => $manual['condition'] ?? null,
                'employee_name' => $manual['employee_name'] ?? null,
                'issue_notes' => $manual['issue_notes'] ?? null,
                'corrective_action' => $manual['corrective_action'] ?? null,
            ]);
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function edit($uuid)
    {
        $report = ReportFragileItem::with('details')->where('uuid', $uuid)->firstOrFail();
        $fragileItems = FragileItem::all();

        return view('report_fragile_item.edit', compact('report', 'fragileItems'))->with('isEdit', true);
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();

        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        $report->details()->delete();

        foreach ($request->items as $item) {
            $report->details()->create([
                'fragile_item_uuid' => $item['fragile_item_uuid'],
                'time_start' => $item['time_start'] ?? 0,
                'time_end' => $item['time_end'] ?? 0,
                'notes' => $item['notes'] ?? 0,
            ]);
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportFragileItem::findOrFail($id);
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
        $report = ReportFragileItem::findOrFail($id);
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
        $report = ReportFragileItem::with(['details.item', 'detailManuals.section'])->where('uuid', $uuid)->firstOrFail();

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

        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_fragile_item');

        return Pdf::loadView('report_fragile_item.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
            'formNumber' => $formNumber,
        ])
            ->setPaper('A4', 'portrait')
            ->stream('Laporan Fragile Item - ' . $report->date . '.pdf');
    }

    public function editNext($uuid)
    {
        $report = ReportFragileItem::with(['details', 'detailManuals.section'])->where('uuid', $uuid)->firstOrFail();
        $fragileItems = FragileItem::all();
        $sections = Section::orderBy('section_name')->get();

        // isEdit = false agar form aktif untuk waktu akhir (time_end)
        return view('report_fragile_item.editnext', compact('report', 'fragileItems', 'sections'))->with('isEdit', true);
    }

    public function updateNext(Request $request, $uuid)
    {
        $report = ReportFragileItem::with('detailManuals')->where('uuid', $uuid)->firstOrFail();

        foreach ($request->items as $uuidItem => $data) {
            $detail = $report->details->where('fragile_item_uuid', $uuidItem)->first();

            if ($detail) {
                $detail->update([
                    'time_start' => $data['time_start'] ?? 0,
                    'time_end' => $data['time_end'] ?? 0,
                    'notes' => $data['notes'] ?? 0,
                ]);
            }
        }

        // Hapus manual item yang di-remove dari form
        $deletedUuids = $request->deleted_manual_uuids ?? [];
        if (!empty($deletedUuids)) {
            DetailFragileItemManual::whereIn('uuid', $deletedUuids)
                ->where('report_fragile_item_uuid', $report->uuid)
                ->delete();
        }

        // Update existing / tambah manual item baru
        foreach ($request->manual_items ?? [] as $manual) {
            if (empty($manual['item_name'])) {
                continue; // skip row kosong
            }

            $payload = [
                'section_uuid' => $manual['section_uuid'] ?? null,
                'sub_area' => $manual['sub_area'] ?? null,
                'item_name' => $manual['item_name'],
                'quantity' => $manual['quantity'] ?? 0,
                'condition' => $manual['condition'] ?? null,
                'employee_name' => $manual['employee_name'] ?? null,
                'issue_notes' => $manual['issue_notes'] ?? null,
                'corrective_action' => $manual['corrective_action'] ?? null,
            ];

            if (!empty($manual['uuid'])) {
                // update existing
                DetailFragileItemManual::where('uuid', $manual['uuid'])
                    ->where('report_fragile_item_uuid', $report->uuid)
                    ->update($payload);
            } else {
                // tambah baru
                DetailFragileItemManual::create(array_merge($payload, [
                    'uuid' => Str::uuid(),
                    'report_fragile_item_uuid' => $report->uuid,
                ]));
            }
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan tahap 2 berhasil diperbarui.');
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
    
        $reports = ReportFragileItem::with(['details.item', 'detailManuals.section'])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Barang_Mudah_Pecah_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new FragileItemExport($reports, $periodLabel), $filename);
    }

}


