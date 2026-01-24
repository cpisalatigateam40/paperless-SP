<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportPremix;
use App\Models\DetailPremix;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPremixController extends Controller
{
    // public function index()
    // {
    //     $reports = ReportPremix::with(['area', 'detailPremixes.premix'])
    //         ->latest()
    //         ->paginate(10);

    //     $reports->getCollection()->transform(function ($report) {
    //         $report->ketidaksesuaian = $report->detailPremixes
    //             ->filter(fn($d) => $d->verification === 'x')
    //             ->count();

    //         return $report;
    //     });

    //     return view('report_premixes.index', compact('reports'));
    // }

    public function index(Request $request)
    {
        $query = ReportPremix::with(['area', 'detailPremixes.premix'])
            ->latest();

        // 🔍 SEARCH (HEADER + DETAIL PREMIX)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 DETAIL PREMIX
                $q->orWhereHas('detailPremixes', function ($dq) use ($search) {

                    // kolom detail_premixes
                    $dq->where('production_code', 'like', "%{$search}%")
                    ->orWhere('used_for_batch', 'like', "%{$search}%")
                    ->orWhere('weight', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('corrective_action', 'like', "%{$search}%")
                    ->orWhere('verification', 'like', "%{$search}%")

                    // 🔥 nama premix dari relasi
                    ->orWhereHas('premix', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%");
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $report->ketidaksesuaian = $report->detailPremixes
                ->filter(fn ($d) => $d->verification === 'x')
                ->count();

            return $report;
        });

        return view('report_premixes.index', compact('reports'));
    }


    public function create()
    {
        $premixes = \App\Models\Premix::orderBy('name')->get();
        return view('report_premixes.create', compact('premixes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:20',

            'details' => 'required|array|min:1',
            'details.*.premix_uuid' => 'required|exists:premixes,uuid',
            'details.*.weight' => 'required|numeric',
            'details.*.used_for_batch' => 'nullable|string|max:255',
            'details.*.notes' => 'nullable|string',
            'details.*.corrective_action' => 'nullable|string',
            'details.*.verification' => 'nullable|string|max:10',
        ]);

        // Simpan header laporan
        $report = ReportPremix::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        // Simpan setiap detail baris premix
        foreach ($request->details as $detail) {
            $premix = \App\Models\Premix::where('uuid', $detail['premix_uuid'])->first();

            DetailPremix::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'premix_uuid' => $detail['premix_uuid'],
                'premix_name' => $premix->name,
                'production_code' => $detail['production_code'] ?? null,
                'weight' => $detail['weight'],
                'used_for_batch' => $detail['used_for_batch'] ?? null,
                'notes' => $detail['notes'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
                'verification' => $detail['verification'] ?? null,
            ]);
        }


        return redirect()->route('report-premixes.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportPremix::where('uuid', $uuid)->firstOrFail();

        // Hapus semua detail terkait
        $report->detailPremixes()->delete();

        // Hapus report utama
        $report->delete();

        return redirect()->route('report-premixes.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function known($id)
    {
        $report = ReportPremix::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve($id)
    {
        $report = ReportPremix::findOrFail($id);
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
        $report = ReportPremix::with(['area', 'detailPremixes.premix'])->where('uuid', $uuid)->firstOrFail();



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

        $pdf = Pdf::loadView('report_premixes.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan_pemeriksaan_premix_' . $report->date->format('Ymd') . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportPremix::where('uuid', $uuid)->firstOrFail();
        $details = DetailPremix::where('report_uuid', $report->uuid)->get();
        $premixes = \App\Models\Premix::orderBy('name')->get();

        return view('report_premixes.edit', compact('report', 'details', 'premixes'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:20',

            'details' => 'required|array|min:1',
            'details.*.premix_uuid' => 'required|exists:premixes,uuid',
            'details.*.weight' => 'required|numeric',
            'details.*.used_for_batch' => 'nullable|string|max:255',
            'details.*.notes' => 'nullable|string',
            'details.*.corrective_action' => 'nullable|string',
            'details.*.verification' => 'nullable|string|max:10',
        ]);

        // 🔹 Update header laporan
        $report = ReportPremix::where('uuid', $uuid)->firstOrFail();
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
            'updated_by' => Auth::user()->name,
        ]);

        // 🔹 Hapus semua detail lama dan ganti dengan data baru
        DetailPremix::where('report_uuid', $report->uuid)->delete();

        // 🔹 Simpan ulang detail dari request
        foreach ($request->details as $detail) {
            $premix = \App\Models\Premix::where('uuid', $detail['premix_uuid'])->first();

            DetailPremix::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'premix_uuid' => $detail['premix_uuid'],
                'premix_name' => $premix->name,
                'production_code' => $detail['production_code'] ?? null,
                'weight' => $detail['weight'],
                'used_for_batch' => $detail['used_for_batch'] ?? null,
                'notes' => $detail['notes'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
                'verification' => $detail['verification'] ?? null,
            ]);
        }

        return redirect()->route('report-premixes.index')->with('success', 'Laporan berhasil diperbarui.');
    }


}