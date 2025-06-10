<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportProcessAreaCleanliness;
use App\Models\DetailProcessAreaCleanliness;
use App\Models\ItemProcessAreaCleanliness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProcessAreaCleanlinessController extends Controller
{
    use HasRoles;

    public function index()
    {
        $reports = ReportProcessAreaCleanliness::with('details.items', 'area')
            ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
                $query->where('area_uuid', Auth::user()->area_uuid);
            })
            ->latest()
            ->get();
        return view('cleanliness_PA.index', compact('reports'));
    }

    public function create()
    {
        return view('cleanliness_PA.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Simpan Report
            $report = ReportProcessAreaCleanliness::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => now()->toDateString(),
                'shift' => $request->shift,
                'section_name' => $request->section_name,
                'created_by' => Auth::user()->name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
                'created_at' => now()->setTimezone('Asia/Jakarta'),
            ]);

            foreach ($request->details as $detailInput) {
                // Simpan Detail
                $detail = DetailProcessAreaCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {
                    $itemName = $itemInput['item'];

                    if ($itemName === 'Suhu ruang (℃)') {
                        $condition = 'Suhu: ' . $itemInput['temperature'] . ' °C,';
                    } else {
                        $condition = $itemInput['condition'];
                    }
                    // Simpan Item
                    ItemProcessAreaCleanliness::create([
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
            return redirect()->route('process-area-cleanliness.index')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $report = ReportProcessAreaCleanliness::where('id', $id)->firstOrFail();
        $report->delete();

        return redirect()->route('process-area-cleanliness.index')->with('success', 'Report berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportProcessAreaCleanliness::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function createDetail(ReportProcessAreaCleanliness $report)
    {
        return view('cleanliness_PA.add-detail', compact('report'));
    }

    public function storeDetail(Request $request, ReportProcessAreaCleanliness $report)
    {
        DB::beginTransaction();
        try {
            foreach ($request->details as $detailInput) {
                $detail = DetailProcessAreaCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {
                    $itemName = $itemInput['item'];

                    $condition = $itemName === 'Suhu ruang (℃)'
                        ? 'Suhu: ' . $itemInput['temperature'] . ' °C,'
                        : $itemInput['condition'];

                    ItemProcessAreaCleanliness::create([
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
            return redirect()->route('process-area-cleanliness.index')->with('success', 'Detail inspeksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function exportPdf($uuid)
    {
        $report = ReportProcessAreaCleanliness::with('area', 'details.items')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('cleanliness_PA.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ]);

        return $pdf->stream('Laporan-Kebersihan-' . $report->date . '.pdf');
    }


}