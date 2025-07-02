<?php

namespace App\Http\Controllers;

use App\Models\ReportPreOperation;
use App\Models\PreOperationMaterial;
use App\Models\PreOperationPackaging;
use App\Models\PreOperationEquipment;
use App\Models\PreOperationRoom;
use App\Models\FollowupPreOperationMaterial;
use App\Models\FollowupPreOperationPackaging;
use App\Models\FollowupPreOperationEquipment;
use App\Models\FollowupPreOperationRoom;
use App\Models\Product;
use App\Models\Area;
use App\Models\Equipment;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPreOperationController extends Controller
{
    public function index()
    {
        $reports = ReportPreOperation::with([
            'product',
            'area',
            'materials',
            'packagings',
            'equipments.equipment',
            'rooms.section'
        ])->latest()->get();
        return view('report_pre_operations.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();
        $equipments = Equipment::all();
        $sections = Section::all();

        $packagingItems = [
            'Casing',
            'Kemasan Plastik',
            'Kemasan Karton',
            'Labelisasi Plastik',
            'Labelisasi Karton',
            'Tusuk Sate',
            'Lakban',
        ];

        return view('report_pre_operations.create', compact(
            'products',
            'areas',
            'equipments',
            'sections',
            'packagingItems'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'product_uuid' => 'required|uuid',
            'production_code' => 'required|string',
            'known_by' => 'nullable|string',
            'approved_by' => 'nullable|string',
        ]);

        $report = ReportPreOperation::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid ?? $request->area_uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        // ✅ Bahan Baku & Penunjang
        foreach ($request->input('materials', []) as $item) {
            if (!empty($item['item'])) {
                $detail = PreOperationMaterial::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'type' => $item['type'] ?? null,
                    'item' => $item['item'] ?? null,
                    'condition' => $item['condition'] ?? null,
                    'corrective_action' => $item['corrective_action'] ?? null,
                    'verification' => $item['verification'] ?? null,
                ]);

                foreach ($item['followups'] ?? [] as $followup) {
                    FollowupPreOperationMaterial::create([
                        'pre_operation_material_uuid' => $detail->uuid,
                        'notes' => $followup['notes'] ?? null,
                        'corrective_action' => $followup['action'] ?? null,
                        'verification' => $followup['verification'] ?? null,
                    ]);
                }
            }
        }

        // ✅ Kemasan
        foreach ($request->input('packagings', []) as $item) {
            if (!empty($item['item'])) {
                $detail = PreOperationPackaging::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'item' => $item['item'] ?? null,
                    'condition' => $item['condition'] ?? null,
                    'corrective_action' => $item['corrective_action'] ?? null,
                    'verification' => $item['verification'] ?? null,
                ]);

                foreach ($item['followups'] ?? [] as $followup) {
                    FollowupPreOperationPackaging::create([
                        'pre_operation_packaging_uuid' => $detail->uuid,
                        'notes' => $followup['notes'] ?? null,
                        'corrective_action' => $followup['action'] ?? null,
                        'verification' => $followup['verification'] ?? null,
                    ]);
                }
            }
        }

        // ✅ Mesin & Peralatan
        foreach ($request->input('equipments', []) as $item) {
            if (!empty($item['equipment_uuid'])) {
                $detail = PreOperationEquipment::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'equipment_uuid' => $item['equipment_uuid'] ?? null,
                    'condition' => $item['condition'] ?? null,
                    'corrective_action' => $item['corrective_action'] ?? null,
                    'verification' => $item['verification'] ?? null,
                ]);

                foreach ($item['followups'] ?? [] as $followup) {
                    FollowupPreOperationEquipment::create([
                        'pre_operation_equipment_uuid' => $detail->uuid,
                        'notes' => $followup['notes'] ?? null,
                        'corrective_action' => $followup['action'] ?? null,
                        'verification' => $followup['verification'] ?? null,
                    ]);
                }
            }
        }

        // ✅ Kondisi Ruangan
        foreach ($request->input('rooms', []) as $item) {
            if (!empty($item['section_uuid'])) {
                $detail = PreOperationRoom::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'section_uuid' => $item['section_uuid'] ?? null,
                    'condition' => $item['condition'] ?? null,
                    'corrective_action' => $item['corrective_action'] ?? null,
                    'verification' => $item['verification'] ?? null,
                ]);

                foreach ($item['followups'] ?? [] as $followup) {
                    FollowupPreOperationRoom::create([
                        'pre_operation_room_uuid' => $detail->uuid,
                        'notes' => $followup['notes'] ?? null,
                        'corrective_action' => $followup['action'] ?? null,
                        'verification' => $followup['verification'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('report_pre_operations.index')->with('success', 'Laporan berhasil disimpan.');
    }


    public function destroy($uuid)
    {
        $report = ReportPreOperation::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportPreOperation::findOrFail($id);
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
        $report = ReportPreOperation::with([
            'product',
            'area',
            'materials',
            'packagings',
            'equipments.equipment',
            'rooms.section',
        ])->where('uuid', $uuid)->firstOrFail();

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

        $pdf = PDF::loadView('report_pre_operations.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('a4', 'portrait');
        return $pdf->stream('Pemeriksaan-Pra-Operasi-' . $report->date . '.pdf');
    }
}