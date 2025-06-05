<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportStorageRmCleanliness;
use App\Models\DetailStorageRmCleanliness;
use App\Models\ItemStorageRmCleanliness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;

class StorageRmCleanlinessController extends Controller
{

    use HasRoles;

    public function index()
    {
        $reports = ReportStorageRmCleanliness::with('details.items', 'area')
            ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
                $query->where('area_uuid', Auth::user()->area_uuid);
            })
            ->latest()
            ->get();

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
                    ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemName,
                        'condition' => $condition,
                        'notes' => $itemInput['notes'] ?? null,
                        'corrective_action' => $itemInput['corrective_action'] ?? null,
                        'verification' => $itemInput['verification'] ?? 0,
                    ]);
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
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
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

                    ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemName,
                        'condition' => $condition,
                        'notes' => $itemInput['notes'] ?? null,
                        'corrective_action' => $itemInput['corrective_action'] ?? null,
                        'verification' => $itemInput['verification'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('cleanliness.index')->with('success', 'Detail inspeksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function exportPdf($uuid)
    {
        $report = ReportStorageRmCleanliness::with('area', 'details.items')->where('uuid', $uuid)->firstOrFail();

        $pdf = Pdf::loadView('cleanliness.pdf', compact('report'));

        return $pdf->stream('Laporan-Kebersihan-' . $report->date . '.pdf');
    }
}