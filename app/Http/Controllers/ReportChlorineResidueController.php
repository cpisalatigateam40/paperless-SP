<?php

namespace App\Http\Controllers;

use App\Models\ReportChlorineResidue;
use App\Models\DetailChlorineResidue;
use App\Models\FollowupChlorineResidue;
use App\Models\Area;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportChlorineResidueController extends Controller
{
    public function index()
    {
        $reports = ReportChlorineResidue::with('section', 'area', 'details')->latest()->get();
        return view('report_chlorine_residues.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        return view('report_chlorine_residues.create', compact('areas', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_uuid' => 'required|exists:sections,uuid',
            'month' => 'required|date',
            'sampling_point' => 'nullable|string',
            'details' => 'required|array',
        ]);

        // Buat header report
        $report = ReportChlorineResidue::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'month' => $request->month . '-01',
            'sampling_point' => $request->sampling_point,
        ]);

        // Simpan detail
        foreach ($request->details as $day => $detail) {
            // Simpan detail
            $detailModel = DetailChlorineResidue::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'day' => $day,
                'result_ppm' => is_numeric($detail['result_ppm']) ? $detail['result_ppm'] : null,
                'remark' => $detail['remark'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
                'verification' => $detail['verification'] ?? null,
                'verified_by' => $detail['verified_by'] ?? null,
                'verified_at' => $detail['verified_at'] ?? null,
            ]);

            // Simpan followups jika ada
            foreach ($detail['followups'] ?? [] as $followup) {
                \App\Models\FollowupChlorineResidue::create([
                    'detail_chlorine_residue_uuid' => $detailModel->uuid,
                    'notes' => $followup['notes'] ?? null,
                    'corrective_action' => $followup['action'] ?? null,
                    'verification' => $followup['verification'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_chlorine_residues.index')->with('success', 'Report berhasil dibuat!');
    }

    public function destroy($uuid)
    {
        $report = ReportChlorineResidue::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_chlorine_residues.index')->with('success', 'Report berhasil dihapus!');
    }

    public function edit($uuid)
    {
        $report = ReportChlorineResidue::with('details')->where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        $sections = Section::all();

        return view('report_chlorine_residues.edit', compact('report', 'areas', 'sections'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'section_uuid' => 'required|exists:sections,uuid',
            'month' => 'required|date',
            'sampling_point' => 'nullable|string',
            'details' => 'required|array',
        ]);

        $report = ReportChlorineResidue::where('uuid', $uuid)->firstOrFail();

        // Update header
        $report->update([
            'section_uuid' => $request->section_uuid,
            'month' => $request->month . '-01',
            'sampling_point' => $request->sampling_point,
        ]);

        foreach ($request->details as $id => $data) {
            $detail = DetailChlorineResidue::findOrFail($id);
            $detail->update([
                'result_ppm' => is_numeric($data['result_ppm']) ? $data['result_ppm'] : null,
                'remark' => $data['remark'] ?? null,
                'corrective_action' => $data['corrective_action'] ?? null,
                'verification' => $data['verification'] ?? null,
                'verified_by' => $data['verified_by'] ?? null,
                'verified_at' => $data['verified_at'] ?? null,
            ]);

            // Hapus followups lama lalu insert ulang
            $detail->followups()->delete();
            foreach ($data['followups'] ?? [] as $followup) {
                FollowupChlorineResidue::create([
                    'detail_chlorine_residue_uuid' => $detail->uuid,
                    'notes' => $followup['notes'] ?? null,
                    'corrective_action' => $followup['corrective_action'] ?? null,
                    'verification' => $followup['verification'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_chlorine_residues.index')->with('success', 'Report berhasil diperbarui!');
    }

    public function approve($id)
    {
        $report = ReportChlorineResidue::findOrFail($id);
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
        $report = ReportChlorineResidue::with('section', 'area', 'details')->where('uuid', $uuid)->firstOrFail();

        // Generate QR untuk approved_by
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $pdf = Pdf::loadView('report_chlorine_residues.pdf', [
            'report' => $report,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('a4', 'portrait'); // bisa landscape kalau tabel panjang

        return $pdf->stream('report-residu-klorin-' . $report->uuid . '.pdf');
    }

}