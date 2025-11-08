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

class StorageRmCleanlinessController extends Controller
{

    use HasRoles;

public function index()
{
    $reports = ReportStorageRmCleanliness::with('details.items.followups', 'area')
        ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
            $query->where('area_uuid', Auth::user()->area_uuid);
        })
        ->latest()
        ->paginate(10);

    // Hitung ketidaksesuaian
    foreach ($reports as $report) {
        $count = 0;

        foreach ($report->details as $detail) {
            foreach ($detail->items as $item) {
                // Jika item tidak OK
                if ($item->verification == 0) {
                    $count++;
                }

                // Jika ada followup dan followup-nya tidak OK juga dihitung
                foreach ($item->followups as $followup) {
                    if ($followup->verification == 0) {
                        $count++;
                    }
                }
            }
        }

        // Tambahkan properti ke model
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
            // Simpan Report
            $report = ReportStorageRmCleanliness::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => now()->toDateString(),
                'shift' => $request->shift,
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
                        $condition = 'Suhu: ' . $itemInput['temperature'] . ' °C, RH: ' . $itemInput['humidity'] . ' %';
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
                        ? 'Suhu: ' . $itemInput['temperature'] . ' °C, RH: ' . $itemInput['humidity'] . ' %'
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
        $report = ReportStorageRmCleanliness::with('area', 'details.items')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);

        return $pdf->stream('Laporan-Kebersihan-' . $report->date . '.pdf');
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
                    $condition = 'Suhu: ' . $itemInput['temperature'] . ' °C, RH: ' . $itemInput['humidity'] . ' %';
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

}