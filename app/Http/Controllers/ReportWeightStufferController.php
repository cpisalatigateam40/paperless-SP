<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportWeightStuffer;
use App\Models\DetailWeightStuffer;
use App\Models\TownsendStuffer;
use App\Models\HitechStuffer;
use App\Models\CaseStuffer;
use App\Models\WeightStuffer;
use App\Models\Product;
use App\Models\StandardStuffer;
use App\Models\WeightStufferMeasurement;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportWeightStufferController extends Controller
{
    public function index()
    {
        $reports = ReportWeightStuffer::with('details')->latest()->paginate(10);
        return view('report_weight_stuffers.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $standards = StandardStuffer::with('product')->get();

        return view('report_weight_stuffers.create', compact('products', 'standards'));
    }

    public function store(Request $request)
    {
        $report = ReportWeightStuffer::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'time' => $detail['time'],
                'weight_standard' => $detail['weight_standard'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
            ]);

            if ($detail['machine'] === 'townsend') {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'hitech') {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if (isset($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        // 'actual_case_1' => $case['actual_case_1'],
                        'actual_case_2' => $case['actual_case_2'],
                    ]);
                }
            }

            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    // loop setiap pasangan weight/long
                    foreach ($weightSet as $key => $value) {
                        // cek kalau key diawali actual_weight_x
                        if (strpos($key, 'actual_weight_') === 0) {
                            $index = str_replace('actual_weight_', '', $key);
                            WeightStufferMeasurement::create([
                                'stuffer_id' => $detailModel->id,
                                'actual_weight' => $value ?? null,
                                'actual_long' => $weightSet['actual_long_' . $index] ?? null,
                            ]);
                        }
                    }
                }
            }

        }

        return redirect()->route('report_weight_stuffers.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();
        $standards = StandardStuffer::all();

        return view('report_weight_stuffers.add-detail', compact('report', 'products', 'standards'));
    }


    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details as $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'time' => $detail['time'],
                'weight_standard' => $detail['weight_standard'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
            ]);

            if ($detail['machine'] === 'townsend') {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'hitech') {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            // Casing
            if (!empty($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        // 'actual_case_1' => $case['actual_case_1'] ?? null,
                        'actual_case_2' => $case['actual_case_2'] ?? null,
                    ]);
                }
            }

            // Berat & Panjang Aktual
            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    // loop setiap pasangan weight/long
                    foreach ($weightSet as $key => $value) {
                        // cek kalau key diawali actual_weight_x
                        if (strpos($key, 'actual_weight_') === 0) {
                            $index = str_replace('actual_weight_', '', $key);
                            WeightStufferMeasurement::create([
                                'stuffer_id' => $detailModel->id,
                                'actual_weight' => $value ?? null,
                                'actual_long' => $weightSet['actual_long_' . $index] ?? null,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()
            ->route('report_weight_stuffers.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }


    public function known($id)
    {
        $report = ReportWeightStuffer::findOrFail($id);
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
        $report = ReportWeightStuffer::findOrFail($id);
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
        $report = ReportWeightStuffer::with([
            'details.product',
            'details.townsend',
            'details.hitech',
            'details.cases',
            'details.weights'
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

        $pdf = Pdf::loadView('report_weight_stuffers.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('A4', 'landscape');
        return $pdf->stream('laporan-verifikasi-berat-stuffer.pdf');
    }

    public function edit($uuid)
{
    $report = ReportWeightStuffer::where('uuid', $uuid)
        ->with([
            'details.product',
            'details.townsend',
            'details.hitech',
            'details.cases',
            'details.weights',
        ])->firstOrFail();

    $products = Product::all();
    $standards = StandardStuffer::with('product')->get();

    return view('report_weight_stuffers.edit', compact('report', 'products', 'standards'));
}

public function update(Request $request, $uuid)
{
    $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();

    // Update header
    $report->update([
        'date'  => $request->date,
        'shift' => $request->shift,
    ]);

    // Hapus detail lama agar bisa replace data baru
    foreach ($report->details as $oldDetail) {
        TownsendStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
        HitechStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
        CaseStuffer::where('stuffer_id', $oldDetail->id)->delete();
        WeightStufferMeasurement::where('stuffer_id', $oldDetail->id)->delete();
        $oldDetail->delete();
    }

    // Simpan detail baru dari form
    foreach ($request->details as $detail) {
        $detailModel = DetailWeightStuffer::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $detail['product_uuid'],
            'production_code' => $detail['production_code'],
            'time' => $detail['time'],
            'weight_standard' => $detail['weight_standard'] ?? null,
            'long_standard' => $detail['long_standard'] ?? null,
        ]);

        if ($detail['machine'] === 'townsend') {
            TownsendStuffer::create([
                'detail_uuid' => $detailModel->uuid,
                'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                'avg_weight' => $detail['avg_weight'] ?? null,
                'avg_long' => $detail['avg_long'] ?? null,
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        if ($detail['machine'] === 'hitech') {
            HitechStuffer::create([
                'detail_uuid' => $detailModel->uuid,
                'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                'avg_weight' => $detail['avg_weight'] ?? null,
                'avg_long' => $detail['avg_long'] ?? null,
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        if (isset($detail['cases'])) {
            foreach ($detail['cases'] as $case) {
                CaseStuffer::create([
                    'stuffer_id' => $detailModel->id,
                    'actual_case_2' => $case['actual_case_2'],
                ]);
            }
        }

        if (isset($detail['weights'])) {
            foreach ($detail['weights'] as $weightSet) {
                foreach ($weightSet as $key => $value) {
                    if (strpos($key, 'actual_weight_') === 0) {
                        $index = str_replace('actual_weight_', '', $key);
                        WeightStufferMeasurement::create([
                            'stuffer_id' => $detailModel->id,
                            'actual_weight' => $value ?? null,
                            'actual_long' => $weightSet['actual_long_' . $index] ?? null,
                        ]);
                    }
                }
            }
        }
    }

    return redirect()->route('report_weight_stuffers.index')->with('success', 'Laporan berhasil diperbarui.');
}

}