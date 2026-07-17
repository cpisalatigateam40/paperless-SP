<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportStorageRmCleanliness;
use App\Models\DetailStorageRmCleanliness;
use App\Models\ItemStorageRmCleanliness;
use App\Models\FollowupCleanlinessStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\StorageRmCleanlinessExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;

class StorageRmCleanlinessController extends Controller
{

    use HasRoles;
    use HasBulkApproval, HasBulkPdfExport;
    protected string $bulkModel = ReportStorageRmCleanliness::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportStorageRmCleanliness::class;
    }

    protected function getBulkExportView(): string
    {
        return 'cleanliness.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'details.items'];
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
        return 'laporan_storage_rm_cleanliness';
    }

    public function index(Request $request)
    {
        $search = $request->search;

        $reports = ReportStorageRmCleanliness::with([
                'details.items.followups',
                'area'
            ])
            ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
                $query->where('area_uuid', Auth::user()->area_uuid);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    /* ================= HEADER ================= */
                    $q->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('room_name', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                    /* ================= AREA ================= */
                    $q->orWhereHas('area', function ($qa) use ($search) {
                        $qa->where('name', 'like', "%{$search}%");
                    });

                    /* ================= DETAIL ================= */
                    $q->orWhereHas('details', function ($qd) use ($search) {
                        $qd->where('inspection_hour', 'like', "%{$search}%")

                        /* ================= ITEM ================= */
                        ->orWhereHas('items', function ($qi) use ($search) {
                            $qi->where('item', 'like', "%{$search}%")
                                ->orWhere('condition', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%")
                                ->orWhere('corrective_action', 'like', "%{$search}%")
                                ->orWhere('verification', 'like', "%{$search}%")

                                /* ============== FOLLOW UP ============== */
                                ->orWhereHas('followups', function ($qf) use ($search) {
                                    $qf->where('notes', 'like', "%{$search}%")
                                        ->orWhere('corrective_action', 'like', "%{$search}%")
                                        ->orWhere('verification', 'like', "%{$search}%");
                                });
                        });
                    });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        /* ================= HITUNG KETIDAKSESUAIAN ================= */
        foreach ($reports as $report) {
            $count = 0;

            foreach ($report->details as $detail) {
                foreach ($detail->items as $item) {
                    if ($item->verification == 0) {
                        $count++;
                    }

                    foreach ($item->followups as $followup) {
                        if ($followup->verification == 0) {
                            $count++;
                        }
                    }
                }
            }

            $report->ketidaksesuaian = $count;
        }

        return view('cleanliness.index', compact('reports'));
    }

    public function create()
    {
        return view('cleanliness.form');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

            // Simpan Report
            $report = ReportStorageRmCleanliness::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
                'room_name' => $request->room_name,
                'created_by' => Auth::user()->name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
                'created_at' => now()->setTimezone('Asia/Jakarta'),
            ]);

            foreach ($request->details as $detailInput) {
                // Simpan Detail
                $detail = DetailStorageRmCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {

                    $itemName = $itemInput['item'];

                    if ($itemName === 'Suhu ruang (℃) / RH (%)') {
                        $condition = 'Suhu: ' . $itemInput['temperature'] . ' °C';

                        if (!empty($itemInput['humidity'])) {
                            $condition .= ', RH: ' . $itemInput['humidity'] . ' %';
                        }
                    } else {
                        $condition = $itemInput['condition'];
                    }
                    // Simpan Item
                    $item = ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemName,
                        'condition' => $condition,
                        'notes' => isset($itemInput['notes'])
                            ? (is_array($itemInput['notes']) ? json_encode($itemInput['notes']) : $itemInput['notes'])
                            : null,
                        'corrective_action' => $itemInput['corrective_action'] ?? null,
                        'verification' => $itemInput['verification'] ?? 0,
                    ]);

                    if (isset($itemInput['followups'])) {
                        foreach ($itemInput['followups'] as $followupInput) {
                            FollowupCleanlinessStorage::create([
                                'item_storage_rm_cleanliness_id' => $item->id,
                                'notes' => $followupInput['notes'] ?? null,
                                'corrective_action' => $followupInput['corrective_action'] ?? null,
                                'verification' => $followupInput['verification'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('cleanliness.index')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $report = ReportStorageRmCleanliness::where('id', $id)->firstOrFail();
        $report->delete();

        return redirect()->route('cleanliness.index')->with('success', 'Report berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportStorageRmCleanliness::findOrFail($id);
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
        $report = ReportStorageRmCleanliness::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function createDetail(ReportStorageRmCleanliness $report)
    {
        return view('cleanliness.add-detail', compact('report'));
    }

    public function storeDetail(Request $request, ReportStorageRmCleanliness $report)
    {
        DB::beginTransaction();
        try {
            foreach ($request->details as $detailInput) {
                // Simpan detail inspeksi
                $detail = DetailStorageRmCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {
                    $itemName = $itemInput['item'];

                    $condition = $itemName === 'Suhu ruang (℃) / RH (%)'
                        ? 'Suhu: ' . $itemInput['temperature'] . ' °C'
                        : $itemInput['condition'];

                    // Simpan item inspeksi
                    $item = ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemName,
                        'condition' => $condition,
                        'notes' => isset($itemInput['notes'])
                            ? (is_array($itemInput['notes']) ? json_encode($itemInput['notes']) : $itemInput['notes'])
                            : null,
                        'corrective_action' => $itemInput['corrective_action'] ?? null,
                        'verification' => $itemInput['verification'] ?? 0,
                    ]);

                    // Simpan koreksi lanjutan jika ada
                    if (isset($itemInput['followups']) && is_array($itemInput['followups'])) {
                        foreach ($itemInput['followups'] as $followupInput) {
                            FollowupCleanlinessStorage::create([
                                'item_storage_rm_cleanliness_id' => $item->id,
                                'notes' => $followupInput['notes'] ?? null,
                                'corrective_action' => $followupInput['corrective_action'] ?? null,
                                'verification' => $followupInput['verification'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('cleanliness.index')->with('success', 'Detail inspeksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }


    public function exportPdf($uuid)
    {
        $report = ReportStorageRmCleanliness::with([
            'area',
            'details.items.followups',
        ])->where('uuid', $uuid)->firstOrFail();

        $createdInfo = "Diperiksa oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQr = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQr = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . optional($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQr = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $pdf = Pdf::loadView('cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQr,
            'knownQr' => $knownQr,
            'approvedQr' => $approvedQr,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('cleanliness_' . $report->uuid . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportStorageRmCleanliness::with([
            'details.items.followups'
        ])->where('uuid', $uuid)->firstOrFail();

        return view('cleanliness.edit', compact('report'));
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            $report = ReportStorageRmCleanliness::where('uuid', $uuid)->firstOrFail();

            $report->update([
                'date' => $request->date,
                'shift' => $request->shift,
                'room_name' => $request->room_name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
            ]);

            // Hapus detail lama & semua item terkait
            foreach ($report->details as $detail) {
                foreach ($detail->items as $item) {
                    $item->followups()->delete();
                }
                $detail->items()->delete();
                $detail->delete();
            }

            // Recreate detail dan item seperti store
            foreach ($request->details as $detailInput) {
                $detail = DetailStorageRmCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {
                    $itemName = $itemInput['item'];

                    if ($itemName === 'Suhu ruang (℃) / RH (%)') {
                        $condition = 'Suhu: ' . $itemInput['temperature'] . ' °C';
                    } else {
                        $condition = $itemInput['condition'];
                    }

                    $item = ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemName,
                        'condition' => $condition,
                        'notes' => isset($itemInput['notes'])
                            ? (is_array($itemInput['notes']) ? json_encode($itemInput['notes']) : $itemInput['notes'])
                            : null,
                        'corrective_action' => $itemInput['corrective_action'] ?? null,
                        'verification' => $itemInput['verification'] ?? 0,
                    ]);

                    if (isset($itemInput['followups'])) {
                        foreach ($itemInput['followups'] as $followupInput) {
                            FollowupCleanlinessStorage::create([
                                'item_storage_rm_cleanliness_id' => $item->id,
                                'notes' => $followupInput['notes'] ?? null,
                                'corrective_action' => $followupInput['corrective_action'] ?? null,
                                'verification' => $followupInput['verification'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('cleanliness.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
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
    
        $reports = ReportStorageRmCleanliness::with(['details.items'])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Kebersihan_Storage_RM_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new StorageRmCleanlinessExport($reports, $periodLabel), $filename);
    }

}