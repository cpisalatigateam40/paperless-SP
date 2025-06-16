<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Area;
use App\Models\ReportGmpEmployee;
use App\Models\DetailGmpEmployee;
use App\Models\SanitationCheck;
use App\Models\SanitationArea;
use App\Models\SanitationResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GmpController extends Controller
{
    use HasRoles;

    public function index()
    {
        $reports = ReportGmpEmployee::with('area')
            ->with('details', 'area', 'sanitationCheck.sanitationArea.sanitationResult')
            ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
                $query->where('area_uuid', Auth::user()->area_uuid);
            })
            ->latest()
            ->get();

        return view('gmp_employee.index', compact('reports'));
    }

    public function create()
    {
        return view('gmp_employee.create', [
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Simpan Laporan Utama GMP
            $report = ReportGmpEmployee::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $request->shift,
                'created_by' => Auth::user()->name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
                'created_at' => now()->setTimezone('Asia/Jakarta'),
            ]);

            // Simpan Detail Inspeksi jika ada
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $detailData = [
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'inspection_hour' => $detail['inspection_hour'] ?? null,
                        'section_name' => $detail['section_name'] ?? null,
                        'employee_name' => $detail['employee_name'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'corrective_action' => $detail['corrective_action'] ?? null,
                        'verification' => $detail['verification'] ?? null,
                    ];
                    DetailGmpEmployee::create($detailData);
                }
            }

            // Simpan Sanitasi jika ada input
            if ($request->has('sanitation.hour_1') || $request->has('sanitation.hour_2')) {
                $checkUUID = Str::uuid();

                // Simpan sanitation_checks
                $sanitationCheck = SanitationCheck::create([
                    'uuid' => $checkUUID,
                    'area_uuid' => Auth::user()->area_uuid,
                    'report_gmp_employee_id' => $report->id,
                    'hour_1' => $request->input('sanitation.hour_1'),
                    'hour_2' => $request->input('sanitation.hour_2'),
                    'verification' => $request->input('sanitation.verification'),
                ]);

                // Simpan sanitation_areas dan sanitation_results
                if ($request->has('sanitation_area')) {
                    foreach ($request->sanitation_area as $area) {
                        $areaUUID = Str::uuid();

                        $sanitationArea = SanitationArea::create([
                            'uuid' => $areaUUID,
                            'sanitation_check_uuid' => $checkUUID,
                            'area_name' => $area['area_name'] ?? null,
                            'chlorine_std' => $area['chlorine_std'] ?? null,
                            'notes' => $area['notes'] ?? null,
                            'corrective_action' => $area['corrective_action'] ?? null,
                        ]);

                        if (isset($area['result'])) {
                            foreach ($area['result'] as $hourTo => $result) {
                                SanitationResult::create([
                                    'sanitation_area_uuid' => $areaUUID,
                                    'hour_to' => $hourTo,
                                    'chlorine_level' => $result['chlorine_level'] ?? null,
                                    'temperature' => $result['temperature'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('gmp-employee.index')->with('success', 'Laporan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($uuid)
    {
        $report = ReportGmpEmployee::with([
            'details',
            'sanitationCheck.sanitationArea.sanitationResult'
        ])->where('uuid', $uuid)->firstOrFail();

        $details = $report->details;
        $sanitation = $report->sanitationCheck;

        // Ambil sanitationArea melalui sanitationCheck
        $sanitationAreas = $sanitation?->sanitationArea?->map(function ($area) {
            $results = $area->sanitationResult ?? collect();
            $area->results_by_hour = $results->keyBy('hour_to');
            return $area;
        }) ?? collect();

        return view('gmp_employee.edit', compact('report', 'details', 'sanitation', 'sanitationAreas'))->with('isEdit', true);
    }



    // public function update(Request $request, $uuid)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $report = ReportGmpEmployee::where('uuid', $uuid)->firstOrFail();

    //         $report->update([
    //             'date' => $request->date,
    //             'shift' => $request->shift,
    //             'known_by' => $request->known_by,
    //             'approved_by' => $request->approved_by,
    //         ]);

    //         // Update detail inspeksi
    //         $report->details()->delete();
    //         if ($request->has('details')) {
    //             foreach ($request->details as $detail) {
    //                 DetailGmpEmployee::create([
    //                     'uuid' => Str::uuid(),
    //                     'report_uuid' => $report->uuid,
    //                     'inspection_hour' => $detail['inspection_hour'] ?? null,
    //                     'section_name' => $detail['section_name'] ?? null,
    //                     'employee_name' => $detail['employee_name'] ?? null,
    //                     'notes' => $detail['notes'] ?? null,
    //                     'corrective_action' => $detail['corrective_action'] ?? null,
    //                     'verification' => $detail['verification'] ?? null,
    //                 ]);
    //             }
    //         }

    //         // Update sanitation
    //         if ($report->sanitationCheck) {
    //             $report->sanitationCheck->sanitationArea()->each(function ($area) {
    //                 $area->sanitationResult()->delete();
    //                 $area->delete();
    //             });
    //             $report->sanitationCheck->delete();
    //         }

    //         if ($request->has('sanitation.hour_1') || $request->has('sanitation.hour_2')) {
    //             $checkUUID = Str::uuid();
    //             $sanitationCheck = SanitationCheck::create([
    //                 'uuid' => $checkUUID,
    //                 'area_uuid' => $report->area_uuid,
    //                 'report_gmp_employee_id' => $report->id,
    //                 'hour_1' => $request->input('sanitation.hour_1'),
    //                 'hour_2' => $request->input('sanitation.hour_2'),
    //                 'verification' => $request->input('sanitation.verification'),
    //             ]);

    //             if ($request->has('sanitation_area')) {
    //                 foreach ($request->sanitation_area as $area) {
    //                     $areaUUID = Str::uuid();

    //                     $sanitationArea = SanitationArea::create([
    //                         'uuid' => $areaUUID,
    //                         'sanitation_check_uuid' => $checkUUID,
    //                         'area_name' => $area['area_name'] ?? null,
    //                         'chlorine_std' => $area['chlorine_std'] ?? null,
    //                         'notes' => $area['notes'] ?? null,
    //                         'corrective_action' => $area['corrective_action'] ?? null,
    //                     ]);

    //                     if (isset($area['result'])) {
    //                         foreach ($area['result'] as $hourTo => $result) {
    //                             SanitationResult::create([
    //                                 'sanitation_area_uuid' => $areaUUID,
    //                                 'hour_to' => $hourTo,
    //                                 'chlorine_level' => $result['chlorine_level'] ?? null,
    //                                 'temperature' => $result['temperature'] ?? null,
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return redirect()->route('gmp-employee.index')->with('success', 'Laporan berhasil diperbarui.');
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    //     }
    // }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();

        try {
            $report = ReportGmpEmployee::where('uuid', $uuid)->firstOrFail();

            $report->update([
                'date' => $request->date,
                'shift' => $request->shift,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
            ]);

            // Update detail inspeksi
            $report->details()->delete();
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    DetailGmpEmployee::create([
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'inspection_hour' => $detail['inspection_hour'] ?? null,
                        'section_name' => $detail['section_name'] ?? null,
                        'employee_name' => $detail['employee_name'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'corrective_action' => $detail['corrective_action'] ?? null,
                        'verification' => $detail['verification'] ?? null,
                    ]);
                }
            }

            // Sanitation Update
            $sanitationCheck = $report->sanitationCheck;

            if ($sanitationCheck) {
                // Update jam 2 dan verifikasi saja
                $sanitationCheck->update([
                    'hour_2' => $request->input('sanitation.hour_2'),
                    'verification' => $request->input('sanitation.verification'),
                ]);

                if ($request->has('sanitation_area')) {
                    foreach ($request->sanitation_area as $areaInput) {
                        $area = $sanitationCheck->sanitationArea()
                            ->where('area_name', $areaInput['area_name'])
                            ->first();

                        if ($area) {
                            // Update std, notes, dan tindakan koreksi jika ada
                            $area->update([
                                'chlorine_std' => $areaInput['chlorine_std'] ?? null,
                                'notes' => $areaInput['notes'] ?? null,
                                'corrective_action' => $areaInput['corrective_action'] ?? null,
                            ]);

                            // Tangani result jam 2
                            if (isset($areaInput['result'][2])) {
                                $result2 = $area->sanitationResult()
                                    ->where('hour_to', 2)->first();

                                if ($result2) {
                                    $result2->update([
                                        'chlorine_level' => $areaInput['result'][2]['chlorine_level'] ?? null,
                                        'temperature' => $areaInput['result'][2]['temperature'] ?? null,
                                    ]);
                                } else {
                                    $area->sanitationResult()->create([
                                        'hour_to' => 2,
                                        'chlorine_level' => $areaInput['result'][2]['chlorine_level'] ?? null,
                                        'temperature' => $areaInput['result'][2]['temperature'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('gmp-employee.index')->with('success', 'Laporan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $report = ReportGmpEmployee::findOrFail($id);
        $report->delete();

        return redirect()->route('gmp-employee.index')->with('success', 'Report dan data sanitasi berhasil dihapus.');
    }

    public function createDetail($reportId)
    {
        $report = ReportGmpEmployee::where('id', $reportId)->firstOrFail();
        return view('gmp_employee.create-detail', compact('report'));
    }

    public function storeDetail(Request $request, $reportId)
    {
        $request->validate([
            'inspection_hour' => 'required',
            'section_name' => 'required',
            'employee_name' => 'required',
            'notes' => 'nullable',
            'corrective_action' => 'nullable',
            'verification' => 'nullable|boolean',
        ]);

        DetailGmpEmployee::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $request->report_uuid,
            'inspection_hour' => $request->inspection_hour,
            'section_name' => $request->section_name,
            'employee_name' => $request->employee_name,
            'notes' => $request->notes,
            'corrective_action' => $request->corrective_action,
            'verification' => $request->verification ? 1 : 0,
        ]);

        return redirect()->route('gmp-employee.index')->with('success', 'Detail inspeksi berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportGmpEmployee::findOrFail($id);
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
        $report = ReportGmpEmployee::with([
            'details',
            'area',
            'sanitationCheck.sanitationArea.sanitationResult'
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


        $pdf = PDF::loadView('gmp_employee.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ]);

        return $pdf->stream('Laporan_GMP_' . $report->date . '.pdf');
    }
}