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
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportWeightStufferController extends Controller
{
    public function index()
    {
        $reports = ReportWeightStuffer::with('details')->latest()->get();
        return view('report_weight_stuffers.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        return view('report_weight_stuffers.create', compact('products'));
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
            ]);

            if (isset($detail['townsend'])) {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['townsend']['stuffer_speed'],
                    'trolley_total' => $detail['townsend']['trolley_total'],
                    'avg_weight' => $detail['townsend']['avg_weight'],
                    'notes' => $detail['townsend']['notes'] ?? null,
                ]);
            }

            if (isset($detail['hitech'])) {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['hitech']['stuffer_speed'],
                    'trolley_total' => $detail['hitech']['trolley_total'],
                    'avg_weight' => $detail['hitech']['avg_weight'],
                    'notes' => $detail['hitech']['notes'] ?? null,
                ]);
            }

            if (isset($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        'actual_case_1' => $case['actual_case_1'],
                        'actual_case_2' => $case['actual_case_2'],
                    ]);
                }
            }

            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weight) {
                    WeightStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        'actual_weight_1' => $weight['actual_weight_1'],
                        'actual_weight_2' => $weight['actual_weight_2'],
                        'actual_weight_3' => $weight['actual_weight_3'] ?? null,
                    ]);
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
        return view('report_weight_stuffers.add-detail', compact('report', 'products'));
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
            ]);

            if (isset($detail['townsend'])) {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['townsend']['stuffer_speed'],
                    'trolley_total' => $detail['townsend']['trolley_total'],
                    'avg_weight' => $detail['townsend']['avg_weight'],
                    'notes' => $detail['townsend']['notes'] ?? null,
                ]);
            }

            if (isset($detail['hitech'])) {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['hitech']['stuffer_speed'],
                    'trolley_total' => $detail['hitech']['trolley_total'],
                    'avg_weight' => $detail['hitech']['avg_weight'],
                    'notes' => $detail['hitech']['notes'] ?? null,
                ]);
            }

            if (isset($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        'actual_case_1' => $case['actual_case_1'],
                        'actual_case_2' => $case['actual_case_2'],
                    ]);
                }
            }

            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weight) {
                    WeightStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        'actual_weight_1' => $weight['actual_weight_1'],
                        'actual_weight_2' => $weight['actual_weight_2'],
                        'actual_weight_3' => $weight['actual_weight_3'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('report_weight_stuffers.index')->with('success', 'Detail berhasil ditambahkan.');
    }
}