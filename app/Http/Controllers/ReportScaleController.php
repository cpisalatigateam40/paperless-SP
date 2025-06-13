<?php

namespace App\Http\Controllers;

use App\Models\ReportScale;
use App\Models\DetailScale;
use App\Models\MeasurementScale;
use App\Models\Scale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportScaleController extends Controller
{
    public function index()
    {
        $reports = ReportScale::with('area')->latest()->paginate(10);
        return view('report_scales.index', compact('reports'));
    }

    public function create()
    {
        $scales = Scale::where('area_uuid', Auth::user()->area_uuid)->get();
        return view('report_scales.create', compact('scales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
            'data' => 'required|array',
        ]);

        $report = ReportScale::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->data as $row) {
            $detail = DetailScale::create([
                'uuid' => Str::uuid(),
                'report_scale_uuid' => $report->uuid,
                'scale_uuid' => $row['scale_uuid'],
                'notes' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                'time_1' => now()->setTimeFromTimeString($row['time_1'] ?? '08:00'),
                'time_2' => now()->setTimeFromTimeString($row['time_2'] ?? '14:00'),
            ]);

            // Pemeriksaan Pukul 1
            foreach ([1000, 5000, 10000] as $weight) {
                MeasurementScale::create([
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 1,
                    'standard_weight' => $weight,
                    'measured_value' => $row['p1_' . $weight],
                ]);
            }

            // Pemeriksaan Pukul 2
            foreach ([1000, 5000, 10000] as $weight) {
                MeasurementScale::create([
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 2,
                    'standard_weight' => $weight,
                    'measured_value' => $row['p2_' . $weight],
                ]);
            }
        }

        return redirect()->route('report-scales.index')->with('success', 'Laporan dan data timbangan berhasil disimpan.');
    }


    public function edit(string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();
        return view('report_scales.edit', compact('report'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
        ]);

        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        return redirect()->route('report-scales.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report-scales.index')->with('success', 'Laporan berhasil dihapus.');
    }

    // ==============================
    // FORM DETAIL TIMBANGAN
    // ==============================

    public function fill(string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();
        $scales = Scale::where('area_uuid', $report->area_uuid)->get();

        return view('report_scales.fill', compact('report', 'scales'));
    }

    public function storeDetail(Request $request, string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();

        foreach ($request->data as $row) {
            $detail = DetailScale::create([
                'uuid' => Str::uuid(),
                'report_scale_uuid' => $report->uuid,
                'scale_uuid' => $row['scale_uuid'],
                'notes' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                'time_1' => now()->setTimeFromTimeString($row['time_1'] ?? '08:00'),
                'time_2' => now()->setTimeFromTimeString($row['time_2'] ?? '14:00'),
            ]);

            // Pemeriksaan Pukul 1
            MeasurementScale::insert([
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 1,
                    'standard_weight' => 1000,
                    'measured_value' => $row['p1_1000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 1,
                    'standard_weight' => 5000,
                    'measured_value' => $row['p1_5000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 1,
                    'standard_weight' => 10000,
                    'measured_value' => $row['p1_10000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Pemeriksaan Pukul 2
            MeasurementScale::insert([
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 2,
                    'standard_weight' => 1000,
                    'measured_value' => $row['p2_1000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 2,
                    'standard_weight' => 5000,
                    'measured_value' => $row['p2_5000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => Str::uuid(),
                    'detail_scale_uuid' => $detail->uuid,
                    'inspection_time_index' => 2,
                    'standard_weight' => 10000,
                    'measured_value' => $row['p2_10000'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        return redirect()->route('report-scales.index')->with('success', 'Detail pemeriksaan berhasil disimpan.');
    }
}