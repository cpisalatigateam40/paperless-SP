<?php

namespace App\Http\Controllers;

use App\Models\ReportScale;
use App\Models\DetailScale;
use App\Models\MeasurementScale;
use App\Models\Scale;
use App\Models\Thermometer;
use App\Models\DetailThermometer;
use App\Models\MeasurementThermometer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class ReportScaleController extends Controller
{
    // public function index()
    // {
    //     $reports = ReportScale::with([
    //         'area',
    //         'details.scale',
    //         'details.measurements',
    //         'thermometerDetails.thermometer',
    //         'thermometerDetails.measurements',
    //     ])->latest()->paginate(10);

    //     return view('report_scales.index', compact('reports'));
    // }
    public function index(Request $request)
    {
        $reports = ReportScale::with([
                'area',
                'details.scale',
                'details.measurements',
                'thermometerDetails.thermometer',
                'thermometerDetails.measurements',
            ])

            // 🔍 FILTER TANGGAL
            ->when($request->date, function ($q) use ($request) {
                $q->whereDate('date', $request->date);
            })

            // 🔍 GLOBAL SEARCH
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($qq) use ($search) {

                    // ===== HEADER REPORT =====
                    $qq->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                    // ===== AREA =====
                    $qq->orWhereHas('area', function ($a) use ($search) {
                        $a->where('name', 'like', "%{$search}%");
                    });

                    // ===== DETAIL SCALE =====
                    $qq->orWhereHas('details', function ($d) use ($search) {

                        $d->where('notes', 'like', "%{$search}%")
                        ->orWhere('time_1', 'like', "%{$search}%")
                        ->orWhere('time_2', 'like', "%{$search}%");

                        // SCALE MASTER
                        $d->orWhereHas('scale', function ($s) use ($search) {
                            $s->where('brand', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                        });

                        // MEASUREMENT SCALE
                        $d->orWhereHas('measurements', function ($m) use ($search) {
                            $m->where('standard_weight', 'like', "%{$search}%")
                            ->orWhere('measured_value', 'like', "%{$search}%");
                        });
                    });

                    // ===== DETAIL THERMOMETER =====
                    $qq->orWhereHas('thermometerDetails', function ($d) use ($search) {

                        $d->where('note', 'like', "%{$search}%")
                        ->orWhere('time_1', 'like', "%{$search}%")
                        ->orWhere('time_2', 'like', "%{$search}%");

                        // THERMOMETER MASTER
                        $d->orWhereHas('thermometer', function ($t) use ($search) {
                            $t->where('brand', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                        });

                        // MEASUREMENT THERMOMETER
                        $d->orWhereHas('measurements', function ($m) use ($search) {
                            $m->where('standard_temperature', 'like', "%{$search}%")
                            ->orWhere('measured_value', 'like', "%{$search}%");
                        });
                    });
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('report_scales.index', compact('reports'));
    }


    public function create()
    {
        $areaUuid = Auth::user()->area_uuid;

        $scales = Scale::where('area_uuid', $areaUuid)->get();
        $thermometers = Thermometer::where('area_uuid', $areaUuid)->get();

        return view('report_scales.create', compact('scales', 'thermometers'))->with('isEdit', false);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
            'data' => 'nullable|array',
            'thermo_data' => 'nullable|array',
        ]);

        $report = ReportScale::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        // ===================== TIMBANGAN =====================
        if ($request->filled('data')) {
            foreach ($request->data as $row) {
                $detail = DetailScale::create([
                    'uuid' => Str::uuid(),
                    'report_scale_uuid' => $report->uuid,
                    'scale_uuid' => $row['scale_uuid'],
                    'notes' => $row['status'],
                    'time_1' => now()->setTimeFromTimeString($row['time_1'] ?? '08:00'),
                    'time_2' => now()->setTimeFromTimeString($row['time_2'] ?? '14:00'),
                ]);

                foreach ([1000, 5000, 10000] as $weight) {
                    // Pemeriksaan 1
                    if (isset($row['p1_' . $weight]) && $row['p1_' . $weight] !== '') {
                        MeasurementScale::create([
                            'uuid' => Str::uuid(),
                            'detail_scale_uuid' => $detail->uuid,
                            'inspection_time_index' => 1,
                            'standard_weight' => (int) $weight,
                            'measured_value' => $row['p1_' . $weight], // nilai user
                        ]);
                    }

                    // Pemeriksaan 2
                    if (isset($row['p2_' . $weight]) && $row['p2_' . $weight] !== '') {
                        MeasurementScale::create([
                            'uuid' => Str::uuid(),
                            'detail_scale_uuid' => $detail->uuid,
                            'inspection_time_index' => 2,
                            'standard_weight' => (int) $weight,
                            'measured_value' => $row['p2_' . $weight], // nilai user
                        ]);
                    }
                }

            }
        }

        // ===================== THERMOMETER =====================
        if ($request->filled('thermo_data')) {
            foreach ($request->thermo_data as $row) {
                $detail = DetailThermometer::create([
                    'uuid' => Str::uuid(),
                    'report_scale_uuid' => $report->uuid,
                    'thermometer_uuid' => $row['thermometer_uuid'],
                    'time_1' => now()->setTimeFromTimeString($row['time_1'] ?? '08:00'),
                    'time_2' => now()->setTimeFromTimeString($row['time_2'] ?? '14:00'),
                    'notes' => $row['status'],
                ]);

                foreach ([0, 100] as $temp) {
                    MeasurementThermometer::create([
                        'uuid' => Str::uuid(),
                        'detail_thermometer_uuid' => $detail->uuid,
                        'inspection_time_index' => 1,
                        'standard_temperature' => $temp,
                        'measured_value' => $row['p1_' . $temp],
                    ]);

                    if (!empty($row['p2_' . $temp])) {
                        MeasurementThermometer::create([
                            'uuid' => Str::uuid(),
                            'detail_thermometer_uuid' => $detail->uuid,
                            'inspection_time_index' => 2,
                            'standard_temperature' => $temp,
                            'measured_value' => $row['p2_' . $temp],
                        ]);
                    }
                }
            }
        }

        return redirect()->route('report-scales.index')->with('success', 'Laporan dan data timbangan berhasil disimpan.');
    }

    public function edit(string $uuid)
    {
        $report = ReportScale::with([
            'details.measurements',
            'thermometerDetails.measurements',
        ])->where('uuid', $uuid)->firstOrFail();

        $scales = Scale::where('area_uuid', $report->area_uuid)->get();
        $thermometers = Thermometer::where('area_uuid', $report->area_uuid)->get();

        return view('report_scales.edit', compact('report', 'scales', 'thermometers'))->with('isEdit', true);
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string|max:50',
            'data' => 'nullable|array',
            'thermo_data' => 'nullable|array',
        ]);

        // Update header
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        // Hapus detail timbangan & thermometer sebelumnya
        $report->details()->delete();
        $report->thermometerDetails()->delete();

        // ===================== SIMPAN TIMBANGAN =====================
        if ($request->filled('data')) {
            foreach ($request->data as $row) {
                $detail = DetailScale::create([
                    'uuid' => Str::uuid(),
                    'report_scale_uuid' => $report->uuid,
                    'scale_uuid' => $row['scale_uuid'],
                    'notes' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                    'time_1' => now()->setTimeFromTimeString($row['time_1']),
                    'time_2' => now()->setTimeFromTimeString($row['time_2']),
                ]);

                foreach ([1000, 5000, 10000] as $weight) {
                    MeasurementScale::create([
                        'uuid' => Str::uuid(),
                        'detail_scale_uuid' => $detail->uuid,
                        'inspection_time_index' => 1,
                        'standard_weight' => $weight,
                        'measured_value' => $row['p1_' . $weight],
                    ]);
                    MeasurementScale::create([
                        'uuid' => Str::uuid(),
                        'detail_scale_uuid' => $detail->uuid,
                        'inspection_time_index' => 2,
                        'standard_weight' => $weight,
                        'measured_value' => $row['p2_' . $weight],
                    ]);
                }
            }
        }

        // ===================== SIMPAN THERMOMETER =====================
        if ($request->filled('thermo_data')) {
            foreach ($request->thermo_data as $row) {
                $detail = DetailThermometer::create([
                    'uuid' => Str::uuid(),
                    'report_scale_uuid' => $report->uuid,
                    'thermometer_uuid' => $row['thermometer_uuid'],
                    'time_1' => now()->setTimeFromTimeString($row['time_1']),
                    'time_2' => now()->setTimeFromTimeString($row['time_2']),
                    'note' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                ]);

                foreach ([0, 100] as $temp) {
                    MeasurementThermometer::create([
                        'uuid' => Str::uuid(),
                        'detail_thermometer_uuid' => $detail->uuid,
                        'inspection_time_index' => 1,
                        'standard_temperature' => $temp,
                        'measured_value' => $row['p1_' . $temp],
                    ]);
                    MeasurementThermometer::create([
                        'uuid' => Str::uuid(),
                        'detail_thermometer_uuid' => $detail->uuid,
                        'inspection_time_index' => 2,
                        'standard_temperature' => $temp,
                        'measured_value' => $row['p2_' . $temp],
                    ]);
                }
            }
        }

        return redirect()->route('report-scales.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $report = ReportScale::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report-scales.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportScale::findOrFail($id);
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
        $report = ReportScale::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function exportPdf(string $uuid)
    {
        $report = ReportScale::with([
            'area',
            'details.scale',
            'details.measurements',
            'thermometerDetails.thermometer',
            'thermometerDetails.measurements',
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

        $pdf = PDF::loadView('report_scales.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);
        return $pdf->stream('Laporan-Timbangan-Thermometer-' . $report->date . '.pdf');
    }

    public function editNext($uuid)
    {
        $report = ReportScale::with([
            'details.measurements',
            'thermometerDetails.measurements',
        ])->where('uuid', $uuid)->firstOrFail();

        $scales = Scale::where('area_uuid', $report->area_uuid)->get();
        $thermometers = Thermometer::where('area_uuid', $report->area_uuid)->get();

        return view('report_scales.editnext', compact('report', 'scales', 'thermometers'))
            ->with('isEdit', true);
    }

    public function updateNext(Request $request, $uuid)
    {
        DB::beginTransaction();

        try {
            $report = ReportScale::where('uuid', $uuid)->firstOrFail();

            // ===================== TIMBANGAN =====================
            if ($request->filled('data')) {
                // Hapus semua detail & measurement lama
                $detailUuids = DetailScale::where('report_scale_uuid', $report->uuid)->pluck('uuid');
                MeasurementScale::whereIn('detail_scale_uuid', $detailUuids)->delete();
                DetailScale::where('report_scale_uuid', $report->uuid)->delete();

                // Simpan ulang
                foreach ($request->data as $row) {
                    $detail = DetailScale::create([
                        'uuid' => Str::uuid(),
                        'report_scale_uuid' => $report->uuid,
                        'scale_uuid' => $row['scale_uuid'],
                        'notes' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                        'time_1' => now()->setTimeFromTimeString($row['time_1']),
                        'time_2' => now()->setTimeFromTimeString($row['time_2']),
                    ]);

                    foreach ([1000, 5000, 10000] as $weight) {
                        // Pemeriksaan ke-1
                        MeasurementScale::create([
                            'uuid' => Str::uuid(),
                            'detail_scale_uuid' => $detail->uuid,
                            'inspection_time_index' => 1,
                            'standard_weight' => $weight,
                            'measured_value' => $row['p1_' . $weight],
                        ]);

                        // Pemeriksaan ke-2
                        MeasurementScale::create([
                            'uuid' => Str::uuid(),
                            'detail_scale_uuid' => $detail->uuid,
                            'inspection_time_index' => 2,
                            'standard_weight' => $weight,
                            'measured_value' => $row['p2_' . $weight],
                        ]);
                    }
                }
            }

            // ===================== THERMOMETER =====================
            if ($request->filled('thermo_data')) {
                $thermoDetailUuids = DetailThermometer::where('report_scale_uuid', $report->uuid)->pluck('uuid');
                MeasurementThermometer::whereIn('detail_thermometer_uuid', $thermoDetailUuids)->delete();
                DetailThermometer::where('report_scale_uuid', $report->uuid)->delete();

                foreach ($request->thermo_data as $row) {
                    $detail = DetailThermometer::create([
                        'uuid' => Str::uuid(),
                        'report_scale_uuid' => $report->uuid,
                        'thermometer_uuid' => $row['thermometer_uuid'],
                        'time_1' => now()->setTimeFromTimeString($row['time_1']),
                        'time_2' => now()->setTimeFromTimeString($row['time_2']),
                        'note' => $row['status'] == '1' ? 'OK' : 'Tidak OK',
                    ]);

                    foreach ([0, 100] as $temp) {
                        // Pemeriksaan ke-1
                        MeasurementThermometer::create([
                            'uuid' => Str::uuid(),
                            'detail_thermometer_uuid' => $detail->uuid,
                            'inspection_time_index' => 1,
                            'standard_temperature' => $temp,
                            'measured_value' => $row['p1_' . $temp],
                        ]);

                        // Pemeriksaan ke-2
                        MeasurementThermometer::create([
                            'uuid' => Str::uuid(),
                            'detail_thermometer_uuid' => $detail->uuid,
                            'inspection_time_index' => 2,
                            'standard_temperature' => $temp,
                            'measured_value' => $row['p2_' . $temp],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('report-scales.index')->with('success', 'Pemeriksaan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}