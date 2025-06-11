<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportFragileItem;
use App\Models\DetailFragileItem;
use App\Models\FragileItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class ReportFragileItemController extends Controller
{
    public function index()
    {
        $reports = ReportFragileItem::latest()->paginate(10);
        return view('report_fragile_item.index', compact('reports'));
    }

    public function create()
    {
        $fragileItems = FragileItem::orderBy('section_name')->get();
        return view('report_fragile_item.create', compact('fragileItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
        ]);

        $report = ReportFragileItem::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->items as $data) {
            DetailFragileItem::create([
                'uuid' => Str::uuid(),
                'report_fragile_item_uuid' => $report->uuid,
                'fragile_item_uuid' => $data['fragile_item_uuid'],
                'time_start' => $data['time_start'] ?? '0',
                'time_end' => $data['time_end'] ?? '0',
                'notes' => $data['notes'] ?? '0',
            ]);
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportFragileItem::findOrFail($id);
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
        $report = ReportFragileItem::with(['details.item'])->where('uuid', $uuid)->firstOrFail();

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

        return Pdf::loadView('report_fragile_item.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait')
            ->stream('Laporan Fragile Item - ' . $report->date . '.pdf');
    }
}