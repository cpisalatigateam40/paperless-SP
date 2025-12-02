<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportReCleanliness;
use App\Models\DetailRoomCleanliness;
use App\Models\DetailEquipmentCleanliness;
use App\Models\FollowupDetailRoomCleanliness;
use App\Models\FollowupDetailEquipmentCleanliness;
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
    $reports = ReportReCleanliness::with([
            'roomDetails.room',
            'roomDetails.element',
            'equipmentDetails.equipment',
            'equipmentDetails.part'
        ])
        ->latest()
        ->paginate(10);

    // hitung ketidaksesuaian berdasarkan verification "Tidak OK"
    $reports->getCollection()->transform(function ($report) {
        $roomIssues = $report->roomDetails
            ->filter(fn($d) => $d->verification === 'Tidak OK')
            ->count();

        $equipmentIssues = $report->equipmentDetails
            ->filter(fn($d) => $d->verification === 'Tidak OK')
            ->count();

        // total ketidaksesuaian
        $report->ketidaksesuaian = $roomIssues + $equipmentIssues;

        return $report;
    });

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

    // public function store(Request $request)
    // {
    //     // Simpan header laporan
    //     $report = ReportReCleanliness::create([
    //         'uuid' => Str::uuid(),
    //         'area_uuid' => Auth::user()->area_uuid,
    //         'date' => $request->date,
    //         'created_by' => Auth::user()->name,
    //     ]);

    //     // Detail room
    //     foreach ($request->input('rooms', []) as $room_uuid => $roomData) {
    //         foreach ($roomData['elements'] ?? [] as $element_uuid => $data) {
    //             $detail = DetailRoomCleanliness::create([
    //                 'uuid' => Str::uuid(),
    //                 'report_re_uuid' => $report->uuid,
    //                 'room_uuid' => $room_uuid,
    //                 'room_element_uuid' => $element_uuid,
    //                 'condition' => $data['condition'] ?? 'dirty',
    //                 'notes' => $data['notes'] ?? null,
    //                 'corrective_action' => $data['corrective_action'] ?? null,
    //                 'verification' => $data['verification'] ?? null,
    //             ]);

    //             // Simpan followups jika ada
    //             if (isset($data['followups']) && is_array($data['followups'])) {
    //                 foreach ($data['followups'] as $followup) {
    //                     \App\Models\FollowupDetailRoomCleanliness::create([
    //                         'detail_room_uuid' => $detail->uuid,
    //                         'notes' => $followup['notes'] ?? null,
    //                         'corrective_action' => $followup['action'] ?? null,
    //                         'verification' => $followup['verification'] ?? null,
    //                     ]);
    //                 }
    //             }
    //         }
    //     }

    //     // Detail equipment
    //     foreach ($request->input('equipments', []) as $equipment_uuid => $equipmentData) {
    //         foreach ($equipmentData['parts'] ?? [] as $part_uuid => $data) {
    //             $detail = DetailEquipmentCleanliness::create([
    //                 'uuid' => Str::uuid(),
    //                 'report_re_uuid' => $report->uuid,
    //                 'equipment_uuid' => $equipment_uuid,
    //                 'equipment_part_uuid' => $part_uuid,
    //                 'condition' => $data['condition'] ?? 'dirty',
    //                 'notes' => $data['notes'] ?? null,
    //                 'corrective_action' => $data['corrective_action'] ?? null,
    //                 'verification' => $data['verification'] ?? null,
    //             ]);

    //             if (isset($data['followups']) && is_array($data['followups'])) {
    //                 foreach ($data['followups'] as $followup) {
    //                     \App\Models\FollowupDetailEquipmentCleanliness::create([
    //                         'detail_equipment_uuid' => $detail->uuid,
    //                         'notes' => $followup['notes'] ?? null,
    //                         'corrective_action' => $followup['action'] ?? null,
    //                         'verification' => $followup['verification'] ?? null,
    //                     ]);
    //                 }
    //             }
    //         }
    //     }

    //     return redirect()->route('report-re-cleanliness.index')->with('success', 'Laporan berhasil disimpan.');
    // }

public function store(Request $request)
{
    // Simpan header laporan
    $report = ReportReCleanliness::create([
        'uuid' => Str::uuid(),
        'area_uuid' => Auth::user()->area_uuid,
        'date' => $request->date,
        'created_by' => Auth::user()->name,
    ]);

    /** ====================== ROOM ====================== **/
foreach ($request->input('rooms', []) as $room_uuid => $roomData) {

    if (!empty($roomData['elements'])) {

        foreach ($roomData['elements'] as $element_uuid => $data) {

            $condition = $data['condition'] === 'clean' ? 'clean' : 'dirty';

            $detail = DetailRoomCleanliness::create([
                'uuid' => Str::uuid(),
                'report_re_uuid' => $report->uuid,
                'room_uuid' => $room_uuid,
                'room_element_uuid' => $element_uuid,
                'condition' => $condition,
                'notes' => $data['notes'] ?? null,
                'corrective_action' => $data['corrective_action'] ?? null,
                'verification' => $data['verification'] ?? null,
            ]);
        }

    } else {
    // Tidak ada elements → cek apakah checkbox room level di-centang
    $condition = isset($roomData['condition']) && $roomData['condition'] === 'clean'
        ? 'clean'
        : 'dirty';

    DetailRoomCleanliness::create([
        'uuid' => Str::uuid(),
        'report_re_uuid' => $report->uuid,
        'room_uuid' => $room_uuid,
        'room_element_uuid' => null,
        'condition' => $condition,
    ]);
}

}


/** ====================== EQUIPMENT ====================== **/
foreach ($request->input('equipments', []) as $equipment_uuid => $equipmentData) {

    if (!empty($equipmentData['parts'])) {

        foreach ($equipmentData['parts'] as $part_uuid => $data) {

            $condition = $data['condition'] === 'clean' ? 'clean' : 'dirty';

            $detail = DetailEquipmentCleanliness::create([
                'uuid' => Str::uuid(),
                'report_re_uuid' => $report->uuid,
                'equipment_uuid' => $equipment_uuid,
                'equipment_part_uuid' => $part_uuid,
                'condition' => $condition,
                'notes' => $data['notes'] ?? null,
                'corrective_action' => $data['corrective_action'] ?? null,
                'verification' => $data['verification'] ?? null,
            ]);
        }

} else {
    $condition = isset($equipmentData['condition']) && $equipmentData['condition'] === 'clean'
        ? 'clean'
        : 'dirty';

    DetailEquipmentCleanliness::create([
        'uuid' => Str::uuid(),
        'report_re_uuid' => $report->uuid,
        'equipment_uuid' => $equipment_uuid,
        'equipment_part_uuid' => null,
        'condition' => $condition,
    ]);
}

}

    return redirect()->route('report-re-cleanliness.index')
        ->with('success', 'Laporan berhasil disimpan.');
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

    public function known($id)
    {
        $report = ReportReCleanliness::findOrFail($id);
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

        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $pdf = Pdf::loadView('report_re_cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_kebersihan_' . $report->date . '.pdf');
    }

    public function edit($uuid)
{
    $report = ReportReCleanliness::with([
        'roomDetails.followups',
        'equipmentDetails.followups'
    ])->where('uuid', $uuid)->firstOrFail();

    return view('report_re_cleanliness.edit', [
        'report' => $report,
        'rooms' => Room::with('elements')->get(),
        'equipments' => Equipment::with('parts')->get(),
    ]);
}


// public function update(Request $request, $uuid)
// {
//     $report = ReportReCleanliness::where('uuid', $uuid)->firstOrFail();

//     // Update header
//     $report->update([
//         'date' => $request->date,
//         'updated_by' => Auth::user()->name,
//     ]);

//     // Hapus detail lama (agar tidak duplikat)
//     DetailRoomCleanliness::where('report_re_uuid', $report->uuid)->delete();
//     DetailEquipmentCleanliness::where('report_re_uuid', $report->uuid)->delete();

//     // Simpan ulang data detail room
//     foreach ($request->input('rooms', []) as $room_uuid => $roomData) {
//         foreach ($roomData['elements'] ?? [] as $element_uuid => $data) {
//             $detail = DetailRoomCleanliness::create([
//                 'uuid' => Str::uuid(),
//                 'report_re_uuid' => $report->uuid,
//                 'room_uuid' => $room_uuid,
//                 'room_element_uuid' => $element_uuid,
//                 'condition' => $data['condition'] ?? 'dirty',
//                 'notes' => $data['notes'] ?? null,
//                 'corrective_action' => $data['corrective_action'] ?? null,
//                 'verification' => $data['verification'] ?? null,
//             ]);

//             if (isset($data['followups'])) {
//                 foreach ($data['followups'] as $followup) {
//                     FollowupDetailRoomCleanliness::create([
//                         'detail_room_uuid' => $detail->uuid,
//                         'notes' => $followup['notes'] ?? null,
//                         'corrective_action' => $followup['action'] ?? null,
//                         'verification' => $followup['verification'] ?? null,
//                     ]);
//                 }
//             }
//         }
//     }

//     // Simpan ulang data detail equipment
//     foreach ($request->input('equipments', []) as $equipment_uuid => $equipmentData) {
//         foreach ($equipmentData['parts'] ?? [] as $part_uuid => $data) {
//             $detail = DetailEquipmentCleanliness::create([
//                 'uuid' => Str::uuid(),
//                 'report_re_uuid' => $report->uuid,
//                 'equipment_uuid' => $equipment_uuid,
//                 'equipment_part_uuid' => $part_uuid,
//                 'condition' => $data['condition'] ?? 'dirty',
//                 'notes' => $data['notes'] ?? null,
//                 'corrective_action' => $data['corrective_action'] ?? null,
//                 'verification' => $data['verification'] ?? null,
//             ]);

//             if (isset($data['followups'])) {
//                 foreach ($data['followups'] as $followup) {
//                     FollowupDetailEquipmentCleanliness::create([
//                         'detail_equipment_uuid' => $detail->uuid,
//                         'notes' => $followup['notes'] ?? null,
//                         'corrective_action' => $followup['action'] ?? null,
//                         'verification' => $followup['verification'] ?? null,
//                     ]);
//                 }
//             }
//         }
//     }

//     return redirect()->route('report-re-cleanliness.index')->with('success', 'Laporan berhasil diperbarui.');
// }

public function update(Request $request, $uuid)
{
    $report = ReportReCleanliness::where('uuid', $uuid)->firstOrFail();

    // Update header
    $report->update([
        'date' => $request->date,
        'updated_by' => Auth::user()->name,
    ]);

    // Hapus detail lama (agar tidak duplikat)
    FollowupDetailRoomCleanliness::whereIn('detail_room_uuid',
        DetailRoomCleanliness::where('report_re_uuid', $report->uuid)->pluck('uuid')
    )->delete();

    FollowupDetailEquipmentCleanliness::whereIn('detail_equipment_uuid',
        DetailEquipmentCleanliness::where('report_re_uuid', $report->uuid)->pluck('uuid')
    )->delete();

    DetailRoomCleanliness::where('report_re_uuid', $report->uuid)->delete();
    DetailEquipmentCleanliness::where('report_re_uuid', $report->uuid)->delete();


    /** ====================== ROOM ====================== **/
    foreach ($request->input('rooms', []) as $room_uuid => $roomData) {

        if (!empty($roomData['elements'])) {
            foreach ($roomData['elements'] as $element_uuid => $data) {

                $detail = DetailRoomCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_re_uuid' => $report->uuid,
                    'room_uuid' => $room_uuid,
                    'room_element_uuid' => $element_uuid,
                    'condition' => $data['condition'] ?? 'dirty',
                    'notes' => $data['notes'] ?? null,
                    'corrective_action' => $data['corrective_action'] ?? null,
                    'verification' => $data['verification'] ?? null,
                ]);

                if (!empty($data['followups'])) {
                    foreach ($data['followups'] as $followup) {
                        FollowupDetailRoomCleanliness::create([
                            'detail_room_uuid' => $detail->uuid,
                            'notes' => $followup['notes'] ?? null,
                            'corrective_action' => $followup['action'] ?? null,
                            'verification' => $followup['verification'] ?? null,
                        ]);
                    }
                }
            }
        } else {
            // fallback ketika tidak ada elements
            DetailRoomCleanliness::create([
                'uuid' => Str::uuid(),
                'report_re_uuid' => $report->uuid,
                'room_uuid' => $room_uuid,
                'room_element_uuid' => null,
                'condition' => 'clean',
                'notes' => null,
                'corrective_action' => null,
                'verification' => null,
            ]);
        }
    }


    /** ====================== EQUIPMENT ====================== **/
    foreach ($request->input('equipments', []) as $equipment_uuid => $equipmentData) {

        if (!empty($equipmentData['parts'])) {
            foreach ($equipmentData['parts'] as $part_uuid => $data) {

                $detail = DetailEquipmentCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_re_uuid' => $report->uuid,
                    'equipment_uuid' => $equipment_uuid,
                    'equipment_part_uuid' => $part_uuid,
                    'condition' => $data['condition'] ?? 'dirty',
                    'notes' => $data['notes'] ?? null,
                    'corrective_action' => $data['corrective_action'] ?? null,
                    'verification' => $data['verification'] ?? null,
                ]);

                if (!empty($data['followups'])) {
                    foreach ($data['followups'] as $followup) {
                        FollowupDetailEquipmentCleanliness::create([
                            'detail_equipment_uuid' => $detail->uuid,
                            'notes' => $followup['notes'] ?? null,
                            'corrective_action' => $followup['action'] ?? null,
                            'verification' => $followup['verification'] ?? null,
                        ]);
                    }
                }
            }
        } else {
            // fallback ketika tidak ada parts
            DetailEquipmentCleanliness::create([
                'uuid' => Str::uuid(),
                'report_re_uuid' => $report->uuid,
                'equipment_uuid' => $equipment_uuid,
                'equipment_part_uuid' => null,
                'condition' => 'clean',
                'notes' => null,
                'corrective_action' => null,
                'verification' => null,
            ]);
        }
    }


    return redirect()->route('report-re-cleanliness.index')
        ->with('success', 'Laporan berhasil diperbarui.');
}


}