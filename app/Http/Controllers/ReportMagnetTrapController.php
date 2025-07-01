<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Section;
use App\Models\ReportMagnetTrap;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportMagnetTrapController extends Controller
{
    public function index()
    {
        $reports = ReportMagnetTrap::with('area', 'section', 'details')->latest()->get();
        return view('report_magnet_traps.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        return view('report_magnet_traps.create', compact('areas', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_uuid' => 'required',
            'date' => 'required|date',
            'shift' => 'required',
            'details.*.time' => 'required',
        ]);

        $report = ReportMagnetTrap::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $detail) {
            $path = null;
            if (isset($detail['finding']) && $detail['finding']) {
                $path = $detail['finding']->store('magnet_trap/findings', 'public');
            }

            $report->details()->create([
                'uuid' => Str::uuid(),
                'time' => $detail['time'],
                'source' => $detail['source'] ?? null,
                'finding_image' => $path,
                'note' => $detail['note'] ?? null,
            ]);
        }

        return redirect()->route('report_magnet_traps.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportMagnetTrap::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report_magnet_traps.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetail($uuid)
    {
        $report = ReportMagnetTrap::with('area', 'section')->where('uuid', $uuid)->firstOrFail();
        return view('report_magnet_traps.add_detail', compact('report'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $request->validate([
            'time' => 'required',
            'source' => 'required|in:QC,Produksi',
            'finding' => 'required|image|mimes:jpeg,png,jpg',
            'note' => 'nullable|string',
        ]);

        $report = ReportMagnetTrap::where('uuid', $uuid)->firstOrFail();

        // Upload file
        $path = $request->file('finding')->store('magnet_trap/findings', 'public');

        $report->details()->create([
            'uuid' => Str::uuid(),
            'time' => $request->time,
            'source' => $request->source,
            'finding_image' => $path, // simpan path gambar
            'note' => $request->note,
        ]);

        return redirect()->route('report_magnet_traps.index')->with('success', 'Detail berhasil ditambahkan.');
    }


    public function approve($id)
    {
        $report = ReportMagnetTrap::findOrFail($id);
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
        $report = ReportMagnetTrap::with('area', 'section', 'details')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_magnet_traps.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Pemeriksaan_MagnetTrap_' . $report->date . '.pdf');
    }
}