<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportQcEquipment;
use App\Models\DetailQcEquipment;
use App\Models\QcEquipment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportQcEquipmentController extends Controller
{
    public function index()
    {
        $reports = ReportQcEquipment::latest()->paginate(10);
        return view('report_qc_equipment.index', compact('reports'));
    }

    public function create()
    {
        $qcEquipments = QcEquipment::orderBy('section_name')->get();
        return view('report_qc_equipment.create', compact('qcEquipments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
        ]);

        $report = ReportQcEquipment::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->items as $data) {
            DetailQcEquipment::create([
                'uuid' => Str::uuid(),
                'report_qc_equipment_uuid' => $report->uuid,
                'qc_equipment_uuid' => $data['qc_equipment_uuid'],
                'time_start' => $data['time_start'] ?? '0',
                'time_end' => $data['time_end'] ?? '0',
                'notes' => $data['notes'] ?? '-',
            ]);
        }

        return redirect()->route('report-qc-equipment.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function edit($uuid)
    {
        $report = ReportQcEquipment::where('uuid', $uuid)->firstOrFail();
        $qcEquipments = QcEquipment::orderBy('section_name')->get();
        $details = DetailQcEquipment::where('report_qc_equipment_uuid', $report->uuid)->get()->keyBy('qc_equipment_uuid');

        return view('report_qc_equipment.edit', compact('report', 'qcEquipments', 'details'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportQcEquipment::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
        ]);

        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->items as $qc_uuid => $data) {
            DetailQcEquipment::updateOrCreate(
                [
                    'report_qc_equipment_uuid' => $report->uuid,
                    'qc_equipment_uuid' => $data['qc_equipment_uuid'],
                ],
                [
                    'time_start' => $data['time_start'] ?? '0',
                    'time_end' => $data['time_end'] ?? '0',
                    'notes' => $data['notes'] ?? '-',
                ]
            );
        }

        return redirect()->route('report-qc-equipment.index')->with('success', 'Laporan berhasil diperbarui.');
    }


    public function destroy($uuid)
    {
        $report = ReportQcEquipment::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report-qc-equipment.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportQcEquipment::findOrFail($id);
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
        $report = ReportQcEquipment::with(['details.item'])->where('uuid', $uuid)->firstOrFail();

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

        return Pdf::loadView('report_qc_equipment.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait')
            ->stream('Laporan QC Equipment - ' . $report->date . '.pdf');
    }
}