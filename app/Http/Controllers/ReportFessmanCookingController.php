<?php

namespace App\Http\Controllers;

use App\Models\ReportFessmanCooking;
use App\Models\DetailFessmanCooking;
use App\Models\FsProcessStep;
use App\Models\FsCoolingDown;
use App\Models\FsSensoryCheck;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportFessmanCookingController extends Controller
{
    public function index()
    {
        $reports = ReportFessmanCooking::with([
            'details.processSteps',
            'details.coolingDowns',
            'details.sensoryCheck'
        ])->latest()->get();
        return view('report_fessman_cookings.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $sections = Section::all();
        return view('report_fessman_cookings.create', compact('products', 'sections'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Buat report utama
            $report = ReportFessmanCooking::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $request['section_uuid'] ?? null,
                'date' => $request['date'],
                'shift' => $request['shift'],
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request['details'] as $detailData) {
                // Skip jika tidak pilih produk
                if (empty($detailData['product_uuid']))
                    continue;

                // Buat detail
                $detail = DetailFessmanCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'] ?? null,
                    'packaging_weight' => $detailData['packaging_weight'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'start_time' => $detailData['start_time'] ?? null,
                    'end_time' => $detailData['end_time'] ?? null,
                ]);

                // Child: process_steps
                if (!empty($detailData['process_steps'])) {
                    foreach ($detailData['process_steps'] as $step) {
                        FsProcessStep::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => $step['step_name'],
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'room_temp_1' => $step['room_temp_1'] ?? null,
                            'room_temp_2' => $step['room_temp_2'] ?? null,
                            'air_circulation_1' => $step['air_circulation_1'] ?? null,
                            'air_circulation_2' => $step['air_circulation_2'] ?? null,
                            'product_temp_1' => $step['product_temp_1'] ?? null,
                            'product_temp_2' => $step['product_temp_2'] ?? null,
                            'actual_product_temp' => $step['actual_product_temp'] ?? null,
                        ]);
                    }
                }

                // Child: sensory_check
                if (!empty($detailData['sensory_check'])) {
                    FsSensoryCheck::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'ripeness' => $detailData['sensory_check']['ripeness'] ?? null,
                        'aroma' => $detailData['sensory_check']['aroma'] ?? null,
                        'taste' => $detailData['sensory_check']['taste'] ?? null,
                        'texture' => $detailData['sensory_check']['texture'] ?? null,
                        'color' => $detailData['sensory_check']['color'] ?? null,
                        'can_be_twisted' => $detailData['sensory_check']['can_be_twisted'] ?? null,
                    ]);
                }

                // Child: tahap_cooling_steps
                if (!empty($detailData['cooling_steps'])) {
                    foreach ($detailData['cooling_steps'] as $step) {
                        FsCoolingDown::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => $step['step_name'],
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'rh_1' => $step['rh_1'] ?? null,
                            'rh_2' => $step['rh_2'] ?? null,
                            'product_temp_after_exit_1' => $step['product_temp_after_exit_1'] ?? null,
                            'product_temp_after_exit_2' => $step['product_temp_after_exit_2'] ?? null,
                            'product_temp_after_exit_3' => $step['product_temp_after_exit_3'] ?? null,
                            'avg_product_temp_after_exit' => $step['avg_product_temp_after_exit'] ?? null,
                            'raw_weight' => $step['raw_weight'] ?? null,
                            'cooked_weight' => $step['cooked_weight'] ?? null,
                            'loss_kg' => $step['loss_kg'] ?? null,
                            'loss_percent' => $step['loss_percent'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('report_fessman_cookings.index')->with('success', 'Laporan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $report = ReportFessmanCooking::where('uuid', $id)->firstOrFail();
        $report->delete();
        return back()->with('success', 'Report deleted.');
    }

    public function edit($uuid)
    {
        $report = ReportFessmanCooking::with([
            'details.product',
            'details.processSteps',
            'details.coolingDowns',
            'details.sensoryCheck'
        ])->where('uuid', $uuid)->firstOrFail();

        $products = Product::all();
        $sections = Section::all();

        return view('report_fessman_cookings.edit', compact('report', 'products', 'sections'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportFessmanCooking::where('uuid', $uuid)->firstOrFail();

        DB::transaction(function () use ($request, $report) {
            $report->update([
                'date' => $request->date,
                'shift' => $request->shift,
                'section_uuid' => $request->section_uuid,
            ]);

            // Hapus detail lama
            foreach ($report->details as $detail) {
                $detail->processSteps()->delete();
                $detail->coolingDowns()->delete();
                $detail->sensoryCheck()->delete();
                $detail->delete();
            }

            // Simpan ulang detail
            foreach ($request->details ?? [] as $detailData) {
                if (empty($detailData['product_uuid']))
                    continue;

                $detail = DetailFessmanCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'] ?? null,
                    'packaging_weight' => $detailData['packaging_weight'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'start_time' => $detailData['start_time'] ?? null,
                    'end_time' => $detailData['end_time'] ?? null,
                ]);

                // Process Steps
                if (!empty($detailData['process_steps'])) {
                    foreach ($detailData['process_steps'] as $step) {
                        FsProcessStep::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => $step['step_name'],
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'room_temp_1' => $step['room_temp_1'] ?? null,
                            'room_temp_2' => $step['room_temp_2'] ?? null,
                            'air_circulation_1' => $step['air_circulation_1'] ?? null,
                            'air_circulation_2' => $step['air_circulation_2'] ?? null,
                            'product_temp_1' => $step['product_temp_1'] ?? null,
                            'product_temp_2' => $step['product_temp_2'] ?? null,
                            'actual_product_temp' => $step['actual_product_temp'] ?? null,
                        ]);
                    }
                }

                // Cooling Steps
                if (!empty($detailData['cooling_steps'])) {
                    foreach ($detailData['cooling_steps'] as $step) {
                        FsCoolingDown::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => $step['step_name'],
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'rh_1' => $step['rh_1'] ?? null,
                            'rh_2' => $step['rh_2'] ?? null,
                            'product_temp_after_exit_1' => $step['product_temp_after_exit_1'] ?? null,
                            'product_temp_after_exit_2' => $step['product_temp_after_exit_2'] ?? null,
                            'product_temp_after_exit_3' => $step['product_temp_after_exit_3'] ?? null,
                            'avg_product_temp_after_exit' => $step['avg_product_temp_after_exit'] ?? null,
                            'raw_weight' => $step['raw_weight'] ?? null,
                            'cooked_weight' => $step['cooked_weight'] ?? null,
                            'loss_kg' => $step['loss_kg'] ?? null,
                            'loss_percent' => $step['loss_percent'] ?? null,
                        ]);
                    }
                }

                // Sensory Check
                if (!empty($detailData['sensory_check'])) {
                    FsSensoryCheck::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'ripeness' => $detailData['sensory_check']['ripeness'] ?? null,
                        'aroma' => $detailData['sensory_check']['aroma'] ?? null,
                        'taste' => $detailData['sensory_check']['taste'] ?? null,
                        'texture' => $detailData['sensory_check']['texture'] ?? null,
                        'color' => $detailData['sensory_check']['color'] ?? null,
                        'can_be_twisted' => $detailData['sensory_check']['can_be_twisted'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('report_fessman_cookings.index')->with('success', 'Laporan berhasil diupdate.');
    }

    public function approve($id)
    {
        $report = ReportFessmanCooking::findOrFail($id);
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
        $report = ReportFessmanCooking::with([
            'details.product',
            'details.processSteps',
            'details.coolingDowns',
            'details.sensoryCheck',
            'section'
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

        $pdf = Pdf::loadView('report_fessman_cookings.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Report_Fessman_Cooking.pdf');
    }


}