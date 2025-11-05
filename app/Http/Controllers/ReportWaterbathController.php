<?php

namespace App\Http\Controllers;

use App\Models\ReportWaterbath;
use App\Models\DetailWaterbath;
use App\Models\PasteurisasiWaterbath;
use App\Models\CoolingShockWaterbath;
use App\Models\DrippingWaterbath;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportWaterbathController extends Controller
{
    public function index()
    {
        $reports = ReportWaterbath::with(['details', 'pasteurisasi', 'coolingShocks', 'drippings'])
            ->latest()
            ->paginate(10);

        return view('report_waterbaths.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::orderBy('product_name')->get();
        return view('report_waterbaths.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Simpan header laporan (report_waterbaths)
        $report = ReportWaterbath::create([
            'uuid'              => Str::uuid(),
            'area_uuid'         => Auth::user()->area_uuid,
            'date'              => $request->date,
            'shift'             => $request->shift,
            'created_by'        => Auth::user()->name,
        ]);

        // Simpan detail_waterbaths
        if ($request->has('details')) {
            foreach ($request->details as $d) {
                DetailWaterbath::create([
                    'uuid'        => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid'=> $d['product_uuid'] ?? null,
                    'batch_code'  => $d['batch_code'] ?? null,
                    'amount'      => $d['amount'] ?? null,
                    'unit'        => $d['unit'] ?? null,
                    'note'        => $d['note'] ?? null,
                ]);
            }
        }

        // Simpan pasteurisasi_waterbaths
        if ($request->has('pasteurisasi')) {
            foreach ($request->pasteurisasi as $p) {
                PasteurisasiWaterbath::create([
                    'uuid'                         => Str::uuid(),
                    'report_uuid'                  => $report->uuid,
                    'initial_product_temp'         => $p['initial_product_temp'] ?? null,
                    'initial_water_temp'           => $p['initial_water_temp'] ?? null,
                    'start_time_pasteur'           => $p['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'            => $p['stop_time_pasteur'] ?? null,
                    'water_temp_after_input_panel' => $p['water_temp_after_input_panel'] ?? null,
                    'water_temp_after_input_actual'=> $p['water_temp_after_input_actual'] ?? null,
                    'water_temp_setting'           => $p['water_temp_setting'] ?? null,
                    'water_temp_actual'            => $p['water_temp_actual'] ?? null,
                    'water_temp_final'             => $p['water_temp_final'] ?? null,
                    'product_temp_final'           => $p['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan cooling_shock_waterbaths
        if ($request->has('cooling_shocks')) {
            foreach ($request->cooling_shocks as $c) {
                CoolingShockWaterbath::create([
                    'uuid'              => Str::uuid(),
                    'report_uuid'       => $report->uuid,
                    'initial_water_temp'=> $c['initial_water_temp'] ?? null,
                    'start_time_pasteur'=> $c['start_time_pasteur'] ?? null,
                    'stop_time_pasteur' => $c['stop_time_pasteur'] ?? null,
                    'water_temp_setting'=> $c['water_temp_setting'] ?? null,
                    'water_temp_actual' => $c['water_temp_actual'] ?? null,
                    'product_temp_final'=> $c['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan dripping_waterbaths
        if ($request->has('drippings')) {
            foreach ($request->drippings as $dr) {
                DrippingWaterbath::create([
                    'uuid'                => Str::uuid(),
                    'report_uuid'         => $report->uuid,
                    'start_time_pasteur'  => $dr['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'   => $dr['stop_time_pasteur'] ?? null,
                    'hot_zone_temperature'=> $dr['hot_zone_temperature'] ?? null,
                    'cold_zone_temperature'=> $dr['cold_zone_temperature'] ?? null,
                    'product_temp_final'  => $dr['product_temp_final'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Report Waterbath berhasil disimpan');
    }

    public function destroy($uuid)
    {
        $report = ReportWaterbath::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Report Waterbath berhasil dihapus');
    }

    public function known($id)
    {
        $report = ReportWaterbath::findOrFail($id);
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
        $report = ReportWaterbath::findOrFail($id);
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
        $report = ReportWaterbath::with(['details.product', 'pasteurisasi', 'coolingShocks', 'drippings'])
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

        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $pdf = Pdf::loadView('report_waterbaths.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('a4', 'landscape');

        $filename = 'report-waterbath-' . Str::slug($report->date . '-shift-' . $report->shift) . '.pdf';

        return $pdf->stream($filename);
    }

    public function addDetail($reportUuid)
    {
        $report = ReportWaterbath::where('uuid', $reportUuid)->firstOrFail();
        $products = Product::orderBy('product_name')->get();

        // Benar: dua string terpisah
        return view('report_waterbaths.add_detail', compact('report', 'products'));

    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportWaterbath::where('uuid', $reportUuid)->firstOrFail();

        // Simpan detail_waterbaths
        if ($request->has('details')) {
            foreach ($request->details as $d) {
                DetailWaterbath::create([
                    'uuid'        => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid'=> $d['product_uuid'] ?? null,
                    'batch_code'  => $d['batch_code'] ?? null,
                    'amount'      => $d['amount'] ?? null,
                    'unit'        => $d['unit'] ?? null,
                    'note'        => $d['note'] ?? null,
                ]);
            }
        }

        // Simpan pasteurisasi_waterbaths
        if ($request->has('pasteurisasi')) {
            foreach ($request->pasteurisasi as $p) {
                PasteurisasiWaterbath::create([
                    'uuid'                         => Str::uuid(),
                    'report_uuid'                  => $report->uuid,
                    'initial_product_temp'         => $p['initial_product_temp'] ?? null,
                    'initial_water_temp'           => $p['initial_water_temp'] ?? null,
                    'start_time_pasteur'           => $p['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'            => $p['stop_time_pasteur'] ?? null,
                    'water_temp_after_input_panel' => $p['water_temp_after_input_panel'] ?? null,
                    'water_temp_after_input_actual'=> $p['water_temp_after_input_actual'] ?? null,
                    'water_temp_setting'           => $p['water_temp_setting'] ?? null,
                    'water_temp_actual'            => $p['water_temp_actual'] ?? null,
                    'water_temp_final'             => $p['water_temp_final'] ?? null,
                    'product_temp_final'           => $p['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan cooling_shock_waterbaths
        if ($request->has('cooling_shocks')) {
            foreach ($request->cooling_shocks as $c) {
                CoolingShockWaterbath::create([
                    'uuid'              => Str::uuid(),
                    'report_uuid'       => $report->uuid,
                    'initial_water_temp'=> $c['initial_water_temp'] ?? null,
                    'start_time_pasteur'=> $c['start_time_pasteur'] ?? null,
                    'stop_time_pasteur' => $c['stop_time_pasteur'] ?? null,
                    'water_temp_setting'=> $c['water_temp_setting'] ?? null,
                    'water_temp_actual' => $c['water_temp_actual'] ?? null,
                    'product_temp_final'=> $c['product_temp_final'] ?? null,
                ]);
            }
        }

        // Simpan dripping_waterbaths
        if ($request->has('drippings')) {
            foreach ($request->drippings as $dr) {
                DrippingWaterbath::create([
                    'uuid'                => Str::uuid(),
                    'report_uuid'         => $report->uuid,
                    'start_time_pasteur'  => $dr['start_time_pasteur'] ?? null,
                    'stop_time_pasteur'   => $dr['stop_time_pasteur'] ?? null,
                    'hot_zone_temperature'=> $dr['hot_zone_temperature'] ?? null,
                    'cold_zone_temperature'=> $dr['cold_zone_temperature'] ?? null,
                    'product_temp_final'  => $dr['product_temp_final'] ?? null,
                ]);
            }
        }

        return redirect()->route('report_waterbaths.index')
            ->with('success', 'Detail tambahan berhasil disimpan');
    }

}