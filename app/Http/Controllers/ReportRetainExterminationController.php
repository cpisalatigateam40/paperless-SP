<?php

namespace App\Http\Controllers;

use App\Models\ReportRetainExtermination;
use App\Models\DetailRetainExtermination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRetainExterminationController extends Controller
{
    public function index()
    {
        $reports = ReportRetainExtermination::withCount('details')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('report_retain_exterminations.index', compact('reports'));
    }

    // Tampilkan form create
    public function create()
    {
        return view('report_retain_exterminations.create');
    }

    // Simpan data
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'details' => 'required|array',
            'details.*.retain_name' => 'required|string',
            'details.*.exp_date' => 'required|date',
            'details.*.retain_condition' => 'required|string',
            'details.*.shape' => 'required|string',
            'details.*.quantity' => 'required|integer',
            'details.*.quantity_kg' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $report = ReportRetainExtermination::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => getShift(),
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detail) {
                $report->details()->create([
                    'uuid' => Str::uuid(),
                    'retain_name' => $detail['retain_name'],
                    'exp_date' => $detail['exp_date'],
                    'retain_condition' => $detail['retain_condition'],
                    'shape' => $detail['shape'],
                    'quantity' => $detail['quantity'],
                    'quantity_kg' => $detail['quantity_kg'],
                    'notes' => $detail['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('report_retain_exterminations.index')
            ->with('success', 'Report berhasil disimpan');
    }

    // Hapus data
    public function destroy($uuid)
    {
        $report = ReportRetainExtermination::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_retain_exterminations.index')
            ->with('success', 'Report berhasil dihapus');
    }

    public function approve($id)
    {
        $report = ReportRetainExtermination::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportRetainExtermination::where('uuid', $uuid)->firstOrFail();
        return view('report_retain_exterminations.add_detail', compact('report'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportRetainExtermination::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'retain_name' => 'required|string',
            'exp_date' => 'required|date',
            'retain_condition' => 'required|string',
            'shape' => 'required|string',
            'quantity' => 'required|integer',
            'quantity_kg' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $report->details()->create([
            'uuid' => Str::uuid(),
            'retain_name' => $request->retain_name,
            'exp_date' => $request->exp_date,
            'retain_condition' => $request->retain_condition,
            'shape' => $request->shape,
            'quantity' => $request->quantity,
            'quantity_kg' => $request->quantity_kg,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_retain_exterminations.index')
            ->with('success', 'Detail berhasil ditambahkan');
    }

    public function exportPdf($uuid)
    {
        $report = ReportRetainExtermination::with('details')
            ->where('uuid', $uuid)
            ->firstOrFail();

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

        $pdf = Pdf::loadView('report_retain_exterminations.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Report-Retain-' . $report->date . '.pdf');
    }
}