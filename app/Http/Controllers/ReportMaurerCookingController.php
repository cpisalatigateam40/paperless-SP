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

        return view('report_maurer_cookings.create', compact('areas', 'sections', 'products'));
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
            $validated = $request->validate([
                'section_uuid' => 'nullable|exists:sections,uuid',
                'date' => 'required|date',
                'shift' => 'required|string|max:10',
                'details' => 'required|array|min:1',
                'details.*.product_uuid' => 'nullable|exists:products,uuid',
                'details.*.production_code' => 'nullable|string',
                'details.*.packaging_weight' => 'nullable|integer',
                'details.*.trolley_count' => 'nullable|integer',
            ]);

            $report = ReportMaurerCooking::create([
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $validated['section_uuid'] ?? null,
                'date' => $validated['date'],
                'shift' => $validated['shift'],
                'created_by' => Auth::user()->name,
            ]);

            foreach ($validated['details'] as $detailData) {
                if (empty($detailData['product_uuid']))
                    continue;

                $detail = DetailMaurerCooking::create([
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detailData['product_uuid'],
                    'production_code' => $detailData['production_code'],
                    'packaging_weight' => $detailData['packaging_weight'],
                    'trolley_count' => $detailData['trolley_count'],
                    'can_be_twisted' => $detailData['can_be_twisted'] ?? null,
                ]);

                // Child tables (check & create if exists)
                if (!empty($detailData['process_steps'])) {
                    foreach ($detailData['process_steps'] as $step) {
                        ShProcessStep::create([
                            'report_detail_uuid' => $detail->uuid,
                            'step_name' => $step['step_name'],
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

                if (!empty($detailData['total_process_time'])) {
                    ShTotalProcessTime::create([
                        'report_detail_uuid' => $detail->uuid,
                        'start_time' => $detailData['total_process_time']['start_time'] ?? null,
                        'end_time' => $detailData['total_process_time']['end_time'] ?? null,
                    ]);
                }

                if (!empty($detailData['thermocouple_positions'])) {
                    foreach ($detailData['thermocouple_positions'] as $pos) {
                        if (!empty($pos['position_info']))
                            ShThermocouplePosition::create([
                                'report_detail_uuid' => $detail->uuid,
                                'position_info' => $pos['position_info'],
                            ]);
                    }
                }

                if (!empty($detailData['sensory_check'])) {
                    ShSensoryCheck::create([
                        'report_detail_uuid' => $detail->uuid,
                        'ripeness' => $detailData['sensory_check']['ripeness'] ?? null,
                        'aroma' => $detailData['sensory_check']['aroma'] ?? null,
                        'texture' => $detailData['sensory_check']['texture'] ?? null,
                        'color' => $detailData['sensory_check']['color'] ?? null,
                    ]);
                }

                if (!empty($detailData['showering_cooling_down'])) {
                    ShoweringCoolingDown::create([
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

                if (!empty($detailData['cooking_losses'])) {
                    foreach ($detailData['cooking_losses'] as $loss) {
                        if (!empty($loss['batch_code']))
                            CookingLoss::create([
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

            DB::commit();

            return redirect()->route('report_maurer_cookings.index')
                ->with('success', 'Report saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}