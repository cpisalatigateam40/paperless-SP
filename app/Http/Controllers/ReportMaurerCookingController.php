<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Section;
use App\Models\Product;
use App\Models\ReportMaurerCooking;
use App\Models\DetailMaurerCooking;
use App\Models\ShProcessStep;
use App\Models\ShTotalProcessTime;
use App\Models\ShThermocouplePosition;
use App\Models\ShSensoryCheck;
use App\Models\ShoweringCoolingDown;
use App\Models\CookingLoss;
use App\Models\MaurerStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportMaurerCookingController extends Controller
{
    public function index()
    {
        $reports = ReportMaurerCooking::latest()->paginate(10);
        return view('report_maurer_cookings.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();

        // Ambil semua data Maurer Standard beserta step-nya
        $maurerStandards = MaurerStandard::with('processStep')->get();

        // Bangun struktur JSON untuk frontend
        $maurerStandardMap = [];

        foreach ($maurerStandards as $standard) {
            $productUuid = $standard->product_uuid;
            $stepName = optional($standard->processStep)->process_name ?? null;

            if (!$stepName)
                continue;

            // Normalisasi step name agar cocok dengan data-step di HTML
            $normalizedStep = strtoupper(str_replace(' ', '', $stepName));

            $maurerStandardMap[$productUuid][$normalizedStep] = [
                'room_temperature_1' => $standard->st_min,
                'room_temperature_2' => $standard->st_max,
                'rh_1' => $standard->rh_min,
                'rh_2' => $standard->rh_max,
                'time_minutes_1' => $standard->time_minute,
                'time_minutes_2' => $standard->time_minute,
                'product_temperature_1' => $standard->ct_min,
                'product_temperature_2' => $standard->ct_max,
            ];
        }

        return view('report_maurer_cookings.create', [
            'areas' => $areas,
            'sections' => $sections,
            'products' => $products,
            'maurerStandards' => $maurerStandards,
            'maurerStandardMap' => $maurerStandardMap, // <- Ini penting
        ]);
    }


    public function destroy($uuid)
    {
        $report = ReportMaurerCooking::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_maurer_cookings.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $report = ReportMaurerCooking::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $request['section_uuid'] ?? null,
                'date' => $request['date'],
                'shift' => $request->shift,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request['details'] as $detailData) {
                // skip jika product_uuid kosong/null
                if (empty($detailData['product_uuid'])) {
                    continue;
                }

                $detail = DetailMaurerCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'] ?? null,
                    'packaging_weight' => $detailData['packaging_weight'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'can_be_twisted' => $detailData['can_be_twisted'] ?? null,
                ]);

                // child: process_steps
                if (!empty($detailData['process_steps'])) {
                    foreach ($detailData['process_steps'] as $step) {
                        // skip kalau step_name kosong
                        if (empty($step['step_name']))
                            continue;

                        ShProcessStep::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => trim($step['step_name']),
                            'room_temperature_1' => $step['room_temperature_1'] ?? null,
                            'room_temperature_2' => $step['room_temperature_2'] ?? null,
                            'rh_1' => $step['rh_1'] ?? null,
                            'rh_2' => $step['rh_2'] ?? null,
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'product_temperature_1' => $step['product_temperature_1'] ?? null,
                            'product_temperature_2' => $step['product_temperature_2'] ?? null,
                        ]);
                    }
                }

                // child: total_process_time
                if (!empty($detailData['total_process_time']['start_time']) || !empty($detailData['total_process_time']['end_time'])) {
                    $startTime = $detailData['total_process_time']['start_time'] ?? null;
                    $endTime = $detailData['total_process_time']['end_time'] ?? null;

                    $duration = null;
                    if ($startTime && $endTime) {
                        // Hitung selisih dalam menit
                        $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                        $end = \Carbon\Carbon::createFromFormat('H:i', $endTime);

                        // Kalau end < start diasumsikan lewat tengah malam
                        if ($end->lessThan($start)) {
                            $end->addDay();
                        }

                        $duration = $start->diffInMinutes($end);
                    }

                    ShTotalProcessTime::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'total_duration' => $duration,
                    ]);
                }

                // child: thermocouple_positions
                if (!empty($detailData['thermocouple_positions'])) {
                    foreach ($detailData['thermocouple_positions'] as $pos) {
                        if (!empty($pos['position_info'])) {
                            ShThermocouplePosition::create([
                                'uuid' => Str::uuid(),
                                'report_detail_uuid' => $detail->uuid,
                                'position_info' => $pos['position_info'],
                            ]);
                        }
                    }
                }

                // child: sensory_check
                if (!empty($detailData['sensory_check'])) {
                    ShSensoryCheck::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'ripeness' => $detailData['sensory_check']['ripeness'] ?? null,
                        'aroma' => $detailData['sensory_check']['aroma'] ?? null,
                        'texture' => $detailData['sensory_check']['texture'] ?? null,
                        'color' => $detailData['sensory_check']['color'] ?? null,
                        'taste' => $detailData['sensory_check']['taste'] ?? null,
                    ]);
                }

                // child: showering_cooling_down
                if (!empty($detailData['showering_cooling_down']['showering_time'])) {
                    ShoweringCoolingDown::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'showering_time' => $detailData['showering_cooling_down']['showering_time'] ?? null,
                        'room_temp_1' => $detailData['showering_cooling_down']['room_temp_1'] ?? null,
                        'room_temp_2' => $detailData['showering_cooling_down']['room_temp_2'] ?? null,
                        'product_temp_1' => $detailData['showering_cooling_down']['product_temp_1'] ?? null,
                        'product_temp_2' => $detailData['showering_cooling_down']['product_temp_2'] ?? null,
                        'time_minutes_1' => $detailData['showering_cooling_down']['time_minutes_1'] ?? null,
                        'time_minutes_2' => $detailData['showering_cooling_down']['time_minutes_2'] ?? null,
                        'product_temp_after_exit_1' => $detailData['showering_cooling_down']['product_temp_after_exit_1'] ?? null,
                        'product_temp_after_exit_2' => $detailData['showering_cooling_down']['product_temp_after_exit_2'] ?? null,
                        'product_temp_after_exit_3' => $detailData['showering_cooling_down']['product_temp_after_exit_3'] ?? null,
                        'avg_product_temp_after_exit' => $detailData['showering_cooling_down']['avg_product_temp_after_exit'] ?? null,
                    ]);
                }

                // child: cooking_losses
                if (!empty($detailData['cooking_losses'])) {
                    foreach ($detailData['cooking_losses'] as $loss) {
                        if (!empty($loss['batch_code'])) {
                            CookingLoss::create([
                                'uuid' => Str::uuid(),
                                'report_detail_uuid' => $detail->uuid,
                                'batch_code' => $loss['batch_code'],
                                'raw_weight' => $loss['raw_weight'] ?? null,
                                'cooked_weight' => $loss['cooked_weight'] ?? null,
                                'loss_kg' => $loss['loss_kg'] ?? null,
                                'loss_percent' => $loss['loss_percent'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('report_maurer_cookings.index')->with('success', 'Report saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($uuid)
    {
        $report = ReportMaurerCooking::with([
            'details.processSteps',
            'details.totalProcessTime',
            'details.thermocouplePositions',
            'details.sensoryCheck',
            'details.showeringCoolingDown',
            'details.cookingLosses',
            'section',
            'area'
        ])->where('uuid', $uuid)->firstOrFail();

        $sections = Section::all();
        $products = Product::all();

        $maurerStandards = MaurerStandard::with('processStep')->get();
        $maurerStandardMap = [];

        foreach ($maurerStandards as $standard) {
            $productUuid = $standard->product_uuid;
            $stepName = optional($standard->processStep)->process_name ?? null;

            if (!$stepName)
                continue;

            // Normalisasi step name agar cocok dengan data-step di HTML
            $normalizedStep = strtoupper(str_replace(' ', '', $stepName));

            $maurerStandardMap[$productUuid][$normalizedStep] = [
                'room_temperature_1' => $standard->st_min,
                'room_temperature_2' => $standard->st_max,
                'rh_1' => $standard->rh_min,
                'rh_2' => $standard->rh_max,
                'time_minutes_1' => $standard->time_minute,
                'time_minutes_2' => $standard->time_minute,
                'product_temperature_1' => $standard->ct_min,
                'product_temperature_2' => $standard->ct_max,
            ];
        }

        return view('report_maurer_cookings.edit', [
            'report' => $report,
            'sections' => $sections,
            'products' => $products,
            'maurerStandards' => $maurerStandards,
            'maurerStandardMap' => $maurerStandardMap, // <- Ini penting
        ]);
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            $report = ReportMaurerCooking::where('uuid', $uuid)->firstOrFail();

            // Update header
            $report->update([
                'section_uuid' => $request['section_uuid'] ?? null,
                'date' => $request['date'],
                'shift' => $request['shift'],
            ]);

            // Hapus semua detail + child
            foreach ($report->details as $detail) {
                $detail->processSteps()->delete();
                $detail->totalProcessTime()->delete();
                $detail->thermocouplePositions()->delete();
                $detail->sensoryCheck()->delete();
                $detail->showeringCoolingDown()->delete();
                $detail->cookingLosses()->delete();
                $detail->delete();
            }

            // Buat ulang detail + child
            foreach ($request['details'] as $detailData) {
                if (empty($detailData['product_uuid']))
                    continue;

                $detail = DetailMaurerCooking::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'] ?? null,
                    'packaging_weight' => $detailData['packaging_weight'] ?? null,
                    'trolley_count' => $detailData['trolley_count'] ?? null,
                    'can_be_twisted' => $detailData['can_be_twisted'] ?? null,
                ]);

                // process_steps
                if (!empty($detailData['process_steps'])) {
                    foreach ($detailData['process_steps'] as $step) {
                        if (empty($step['step_name']))
                            continue;

                        ShProcessStep::create([
                            'uuid' => Str::uuid(),
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => trim($step['step_name']),
                            'room_temperature_1' => $step['room_temperature_1'] ?? null,
                            'room_temperature_2' => $step['room_temperature_2'] ?? null,
                            'rh_1' => $step['rh_1'] ?? null,
                            'rh_2' => $step['rh_2'] ?? null,
                            'time_minutes_1' => $step['time_minutes_1'] ?? null,
                            'time_minutes_2' => $step['time_minutes_2'] ?? null,
                            'product_temperature_1' => $step['product_temperature_1'] ?? null,
                            'product_temperature_2' => $step['product_temperature_2'] ?? null,
                        ]);
                    }
                }

                // total_process_time
                if (!empty($detailData['total_process_time']['start_time']) || !empty($detailData['total_process_time']['end_time'])) {
                    $startTime = $detailData['total_process_time']['start_time'] ?? null;
                    $endTime = $detailData['total_process_time']['end_time'] ?? null;

                    $duration = null;
                    if ($startTime && $endTime) {
                        // Hitung selisih dalam menit
                        $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                        $end = \Carbon\Carbon::createFromFormat('H:i', $endTime);

                        // Kalau end < start diasumsikan lewat tengah malam
                        if ($end->lessThan($start)) {
                            $end->addDay();
                        }

                        $duration = $start->diffInMinutes($end);
                    }

                    ShTotalProcessTime::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'total_duration' => $duration,
                    ]);
                }


                // thermocouple_positions
                if (!empty($detailData['thermocouple_positions'])) {
                    foreach ($detailData['thermocouple_positions'] as $pos) {
                        if (!empty($pos['position_info'])) {
                            ShThermocouplePosition::create([
                                'uuid' => Str::uuid(),
                                'report_detail_uuid' => $detail->uuid,
                                'position_info' => $pos['position_info'],
                            ]);
                        }
                    }
                }

                // sensory_check
                if (!empty($detailData['sensory_check'])) {
                    ShSensoryCheck::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'ripeness' => $detailData['sensory_check']['ripeness'] ?? null,
                        'aroma' => $detailData['sensory_check']['aroma'] ?? null,
                        'texture' => $detailData['sensory_check']['texture'] ?? null,
                        'color' => $detailData['sensory_check']['color'] ?? null,
                        'taste' => $detailData['sensory_check']['taste'] ?? null,
                    ]);
                }

                // showering_cooling_down
                if (!empty($detailData['showering_cooling_down']['showering_time'])) {
                    ShoweringCoolingDown::create([
                        'uuid' => Str::uuid(),
                        'report_detail_uuid' => $detail->uuid,
                        'showering_time' => $detailData['showering_cooling_down']['showering_time'] ?? null,
                        'room_temp_1' => $detailData['showering_cooling_down']['room_temp_1'] ?? null,
                        'room_temp_2' => $detailData['showering_cooling_down']['room_temp_2'] ?? null,
                        'product_temp_1' => $detailData['showering_cooling_down']['product_temp_1'] ?? null,
                        'product_temp_2' => $detailData['showering_cooling_down']['product_temp_2'] ?? null,
                        'time_minutes_1' => $detailData['showering_cooling_down']['time_minutes_1'] ?? null,
                        'time_minutes_2' => $detailData['showering_cooling_down']['time_minutes_2'] ?? null,
                        'product_temp_after_exit_1' => $detailData['showering_cooling_down']['product_temp_after_exit_1'] ?? null,
                        'product_temp_after_exit_2' => $detailData['showering_cooling_down']['product_temp_after_exit_2'] ?? null,
                        'product_temp_after_exit_3' => $detailData['showering_cooling_down']['product_temp_after_exit_3'] ?? null,
                        'avg_product_temp_after_exit' => $detailData['showering_cooling_down']['avg_product_temp_after_exit'] ?? null,
                    ]);
                }

                // cooking_losses
                if (!empty($detailData['cooking_losses'])) {
                    foreach ($detailData['cooking_losses'] as $loss) {
                        if (!empty($loss['batch_code'])) {
                            CookingLoss::create([
                                'uuid' => Str::uuid(),
                                'report_detail_uuid' => $detail->uuid,
                                'batch_code' => $loss['batch_code'],
                                'raw_weight' => $loss['raw_weight'] ?? null,
                                'cooked_weight' => $loss['cooked_weight'] ?? null,
                                'loss_kg' => $loss['loss_kg'] ?? null,
                                'loss_percent' => $loss['loss_percent'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('report_maurer_cookings.index')->with('success', 'Report updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update gagal: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $report = ReportMaurerCooking::findOrFail($id);
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
        $report = ReportMaurerCooking::findOrFail($id);
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
        $report = ReportMaurerCooking::with([
            'details.processSteps',
            'details.totalProcessTime',
            'details.thermocouplePositions',
            'details.sensoryCheck',
            'details.showeringCoolingDown',
            'details.cookingLosses',
            'section',
            'area',
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

        $pdf = Pdf::loadView('report_maurer_cookings.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);

        return $pdf->stream('report-maurer-cooking-' . $report->date . '.pdf');
    }


}