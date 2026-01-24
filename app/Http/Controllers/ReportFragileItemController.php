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
    // public function index()
    // {
    //     $reports = ReportFragileItem::with(['details.item'])->latest()->paginate(10);
    //     return view('report_fragile_item.index', compact('reports'));
    // }
    public function index(Request $request)
    {
        $reports = ReportFragileItem::with([
                'area',
                'details.item'
            ])

            // 🔒 Area user (kalau bukan superadmin)
            ->when(!Auth::user()->hasRole('Superadmin'), function ($q) {
                $q->where('area_uuid', Auth::user()->area_uuid);
            })

            // 🔍 Tanggal
            ->when($request->date, function ($q) use ($request) {
                $q->whereDate('date', $request->date);
            })

            // 🔍 Shift
            ->when($request->shift, function ($q) use ($request) {
                $q->where('shift', $request->shift);
            })

            // 🔍 Global Search (SATU INPUT)
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($qq) use ($search) {

                    // 🔹 Header report
                    $qq->where('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%");

                    // 🔹 Area
                    $qq->orWhereHas('area', function ($a) use ($search) {
                        $a->where('name', 'like', "%{$search}%");
                    });

                    // 🔹 Detail laporan
                    $qq->orWhereHas('details', function ($d) use ($search) {
                        $d->where('notes', 'like', "%{$search}%")
                        ->orWhere('actual_quantity', 'like', "%{$search}%");

                        // 🔹 Master Fragile Item
                        $d->orWhereHas('item', function ($i) use ($search) {
                            $i->where('item_name', 'like', "%{$search}%")
                            ->orWhere('section_name', 'like', "%{$search}%")
                            ->orWhere('owner', 'like', "%{$search}%");
                        });
                    });
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('report_fragile_item.index', compact('reports'));
    }


    public function create()
    {
        $fragileItems = FragileItem::orderBy('section_name')->get();
        return view('report_fragile_item.create', compact('fragileItems'))->with('isEdit', false);
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

    public function edit($uuid)
    {
        $report = ReportFragileItem::with('details')->where('uuid', $uuid)->firstOrFail();
        $fragileItems = FragileItem::all();

        return view('report_fragile_item.edit', compact('report', 'fragileItems'))->with('isEdit', true);
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();

        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        $report->details()->delete();

        foreach ($request->items as $item) {
            $report->details()->create([
                'fragile_item_uuid' => $item['fragile_item_uuid'],
                'time_start' => $item['time_start'] ?? 0,
                'time_end' => $item['time_end'] ?? 0,
                'notes' => $item['notes'] ?? 0,
            ]);
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil diperbarui.');
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

    public function known($id)
    {
        $report = ReportFragileItem::findOrFail($id);
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

         // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        return Pdf::loadView('report_fragile_item.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait')
            ->stream('Laporan Fragile Item - ' . $report->date . '.pdf');
    }

    public function editNext($uuid)
{
    $report = ReportFragileItem::with('details')->where('uuid', $uuid)->firstOrFail();
    $fragileItems = FragileItem::all();

    // isEdit = false agar form aktif untuk waktu akhir (time_end)
    return view('report_fragile_item.editnext', compact('report', 'fragileItems'))->with('isEdit', true);
}

public function updateNext(Request $request, $uuid)
{
    $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();

    foreach ($request->items as $uuidItem => $data) {
        $detail = $report->details->where('fragile_item_uuid', $uuidItem)->first();

        if ($detail) {
            $detail->update([
                'time_start' => $data['time_start'] ?? 0,
                'time_end' => $data['time_end'] ?? 0,
                'notes' => $data['notes'] ?? 0,
            ]);
        }
    }

    return redirect()->route('report-fragile-item.index')->with('success', 'Laporan tahap 2 berhasil diperbarui.');
}

}


