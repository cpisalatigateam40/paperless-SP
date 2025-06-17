<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportReCleanliness;
use App\Models\DetailRoomCleanliness;
use App\Models\DetailEquipmentCleanliness;
use App\Models\RoomElement;
use App\Models\EquipmentPart;
use App\Models\Area;
use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportReCleanlinessController extends Controller
{
    public function index()
    {
        $reports = ReportReCleanliness::with(['roomDetails.room', 'roomDetails.element', 'equipmentDetails.equipment', 'equipmentDetails.part'])
            ->orderByDesc('date')
            ->get();

        return view('report_re_cleanliness.index', compact('reports'));
    }

    public function create()
    {
        return view('report_re_cleanliness.create', [
            'areas' => Area::all(),
            'rooms' => Room::with('elements')->get(),
            'equipments' => Equipment::with('parts')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Simpan header laporan
        $report = ReportReCleanliness::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail ruangan
        foreach ($request->input('rooms', []) as $room_uuid => $roomData) {
            foreach ($roomData['elements'] ?? [] as $element_uuid => $data) {
                DetailRoomCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_re_uuid' => $report->uuid,
                    'room_uuid' => $room_uuid,
                    'room_element_uuid' => $element_uuid,
                    'condition' => $data['condition'] ?? 'dirty',
                    'notes' => $data['notes'] ?? null,
                    'corrective_action' => $data['corrective_action'] ?? null,
                    'verification' => $data['verification'] ?? null,
                ]);
            }
        }

        // Simpan detail equipment
        foreach ($request->input('equipments', []) as $equipment_uuid => $equipmentData) {
            foreach ($equipmentData['parts'] ?? [] as $part_uuid => $data) {
                DetailEquipmentCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_re_uuid' => $report->uuid,
                    'equipment_uuid' => $equipment_uuid,
                    'equipment_part_uuid' => $part_uuid,
                    'condition' => $data['condition'] ?? 'dirty',
                    'notes' => $data['notes'] ?? null,
                    'corrective_action' => $data['corrective_action'] ?? null,
                    'verification' => $data['verification'] ?? null,
                ]);
            }
        }

        return redirect()->route('report-re-cleanliness.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportReCleanliness::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report-re-cleanliness.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportReCleanliness::findOrFail($id);
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
        $report = ReportReCleanliness::with([
            'roomDetails.room',
            'roomDetails.element',
            'equipmentDetails.equipment',
            'equipmentDetails.part',
            'area'
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

        $pdf = Pdf::loadView('report_re_cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_kebersihan_' . $report->date . '.pdf');
    }
}