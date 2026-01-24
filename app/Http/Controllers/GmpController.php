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
use App\Models\FollowupGmpEmployee;
use App\Models\FollowupSanitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GmpController extends Controller
{
    use HasRoles;

    // public function index()
    // {
    //     $reports = ReportGmpEmployee::with([
    //         'area',
    //         'details.followups',
    //         'sanitationCheck.sanitationArea.followups'
    //     ])
    //     ->when(!Auth::user()->hasRole('Superadmin'), function ($query) {
    //         $query->where('area_uuid', Auth::user()->area_uuid);
    //     })
    //     ->latest()
    //     ->paginate(10);

    //     // 🔹 Hitung ketidaksesuaian untuk tiap laporan
    //     foreach ($reports as $report) {
    //         $count = 0;

    //         // 🧍 Detail pegawai
    //         foreach ($report->details as $detail) {
    //             if ($detail->verification == 0) {
    //                 $count++;
    //             }

    //             // follow-up pegawai
    //             foreach ($detail->followups as $f) {
    //                 if ($f->verification == 0) {
    //                     $count++;
    //                 }
    //             }
    //         }

    //         // 🧽 Sanitasi area
    //         if ($report->sanitationCheck) {
    //             foreach ($report->sanitationCheck->sanitationArea as $area) {
    //                 if ($area->verification == 0) {
    //                     $count++;
    //                 }

    //                 // follow-up sanitasi
    //                 foreach ($area->followups as $f) {
    //                     if ($f->verification == 0) {
    //                         $count++;
    //                     }
    //                 }
    //             }
    //         }

    //         // Tambahkan properti dinamis
    //         $report->ketidaksesuaian = $count;
    //     }

    //     return view('gmp_employee.index', compact('reports'));
    // }

    public function index(Request $request)
    {
        $reports = ReportGmpEmployee::with([
            'area',
            'details.followups',
            'sanitationCheck.sanitationArea.followups'
        ])
        ->when(!Auth::user()->hasRole('Superadmin'), function ($q) {
            $q->where('area_uuid', Auth::user()->area_uuid);
        })

        // 🔍 FILTER GLOBAL
        ->when(
            $request->filled('date') ||
            $request->filled('shift') ||
            $request->filled('keyword') ||
            $request->filled('sanitation_area') ||
            $request->boolean('only_nc'),
            function ($q) use ($request) {

            $q->where(function ($qq) use ($request) {

                // 📅 tanggal
                if ($request->filled('date')) {
                    $qq->whereDate('date', $request->date);
                }

                // 🔄 shift
                if ($request->filled('shift')) {
                    $qq->where('shift', $request->shift);
                }

                // 👤 pegawai / section
                if ($request->filled('keyword')) {
                    $qq->orWhereHas('details', function ($d) use ($request) {
                        $d->where('employee_name', 'like', "%{$request->keyword}%")
                        ->orWhere('section_name', 'like', "%{$request->keyword}%")
                        ->orWhere('notes', 'like', "%{$request->keyword}%");
                    });
                }

                // 🧽 area sanitasi
                if ($request->filled('sanitation_area')) {
                    $qq->orWhereHas('sanitationCheck.sanitationArea', function ($s) use ($request) {
                        $s->where('area_name', 'like', "%{$request->sanitation_area}%");
                    });
                }

                // ❌ hanya NC
                if ($request->boolean('only_nc')) {
                    $qq->orWhereHas('details', fn ($d) => $d->where('verification', 0))
                    ->orWhereHas('details.followups', fn ($f) => $f->where('verification', 0))
                    ->orWhereHas('sanitationCheck.sanitationArea', fn ($s) => $s->where('verification', 0))
                    ->orWhereHas('sanitationCheck.sanitationArea.followups', fn ($f) => $f->where('verification', 0));
                }
            });
        })

        ->latest()
        ->paginate(10)
        ->withQueryString();

        // 🔢 hitung ketidaksesuaian (AMAN)
        foreach ($reports as $report) {
            $count = 0;

            foreach ($report->details as $d) {
                if ($d->verification == 0) $count++;
                foreach ($d->followups as $f) {
                    if ($f->verification == 0) $count++;
                }
            }

            if ($report->sanitationCheck) {
                foreach ($report->sanitationCheck->sanitationArea as $a) {
                    if ($a->verification == 0) $count++;
                    foreach ($a->followups as $f) {
                        if ($f->verification == 0) $count++;
                    }
                }
            }

            $report->ketidaksesuaian = $count;
        }

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
                    $detailData = DetailGmpEmployee::create([
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'inspection_hour' => $detail['inspection_hour'] ?? null,
                        'section_name' => $detail['section_name'] ?? null,
                        'employee_name' => $detail['employee_name'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'corrective_action' => $detail['corrective_action'] ?? null,
                        'verification' => $detail['verification'] ?? null,
                    ]);

                    // ✅ Simpan followups jika ada
                    if (isset($detail['followups']) && is_array($detail['followups'])) {
                        foreach ($detail['followups'] as $followup) {
                            FollowupGmpEmployee::create([
                                'gmp_employee_detail_id' => $detailData->id,
                                'notes' => $followup['notes'] ?? null,
                                'action' => $followup['action'] ?? null,
                                'verification' => $followup['verification'] ?? 0,
                            ]);
                        }
                    }
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
                            'verification' => $area['verification'] ?? null,
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

                        // ✅ Simpan followups jika ada
                        if (isset($area['followups']) && is_array($area['followups'])) {
                            foreach ($area['followups'] as $followup) {
                                FollowupSanitation::create([
                                    'sanitation_area_uuid' => $areaUUID,
                                    'notes' => $followup['notes'] ?? null,
                                    'action' => $followup['action'] ?? null,
                                    'verification' => $followup['verification'] ?? 0,
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

            $report->details()->delete();

            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $newDetail = DetailGmpEmployee::create([
                        'uuid' => Str::uuid(),
                        'report_uuid' => $report->uuid,
                        'inspection_hour' => $detail['inspection_hour'] ?? null,
                        'section_name' => $detail['section_name'] ?? null,
                        'employee_name' => $detail['employee_name'] ?? null,
                        'notes' => $detail['notes'] ?? null,
                        'corrective_action' => $detail['corrective_action'] ?? null,
                        'verification' => $detail['verification'] ?? null,
                    ]);

                    if (!empty($detail['followups'])) {
                        foreach ($detail['followups'] as $followup) {
                            $newDetail->followups()->create([
                                'notes' => $followup['notes'] ?? null,
                                'action' => $followup['action'] ?? null,
                                'verification' => $followup['verification'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // =========================
            // Update sanitation check
            // =========================
            $sanitationCheck = $report->sanitationCheck;

            if ($sanitationCheck) {
                // Update jam_2
                $sanitationCheck->update([
                    'hour_2' => $request->input('sanitation.hour_2'),
                ]);

                if ($request->has('sanitation_area')) {
                    foreach ($request->sanitation_area as $areaInput) {
                        $area = $sanitationCheck->sanitationArea()
                            ->where('area_name', $areaInput['area_name'])
                            ->first();

                        if ($area) {
                            // Update std, notes, corrective action, verification
                            $area->update([
                                'chlorine_std' => $areaInput['chlorine_std'] ?? null,
                                'notes' => $areaInput['notes'] ?? null,
                                'corrective_action' => $areaInput['corrective_action'] ?? null,
                                'verification' => $areaInput['verification'] ?? null,
                            ]);

                            // Update / insert sanitation result jam_2
                            if (isset($areaInput['result'][2])) {
                                $result2 = $area->sanitationResult()->where('hour_to', 2)->first();

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

                            // Hapus followups lama dulu, baru tambahkan ulang followups baru
                            $area->followups()->delete();

                            if (!empty($areaInput['followups'])) {
                                foreach ($areaInput['followups'] as $followup) {
                                    $area->followups()->create([
                                        'notes' => $followup['notes'] ?? null,
                                        'action' => $followup['action'] ?? null,
                                        'verification' => $followup['verification'] ?? null,
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

        DB::beginTransaction();
        try {
            // Simpan detail inspeksi utama
            $detail = DetailGmpEmployee::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $request->report_uuid,
                'inspection_hour' => $request->inspection_hour,
                'section_name' => $request->section_name,
                'employee_name' => $request->employee_name,
                'notes' => $request->notes,
                'corrective_action' => $request->corrective_action,
                'verification' => $request->verification ? 1 : 0,
            ]);

            // Jika ada followups dikirim dari form
            if ($request->has('followups') && is_array($request->followups)) {
                foreach ($request->followups as $followup) {
                    if (!empty($followup['notes']) || !empty($followup['action'])) {
                        \App\Models\FollowupGmpEmployee::create([
                            'gmp_employee_detail_id' => $detail->id,
                            'notes' => $followup['notes'] ?? null,
                            'action' => $followup['action'] ?? null,
                            'verification' => isset($followup['verification']) && $followup['verification'] ? 1 : 0,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('gmp-employee.index')->with('success', 'Detail inspeksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function known($id)
    {
        $report = ReportGmpEmployee::findOrFail($id);
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

        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);


        $pdf = PDF::loadView('gmp_employee.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);

        return $pdf->stream('Laporan_GMP_' . $report->date . '.pdf');
    }

    public function editNext($uuid)
    {
        $report = ReportGmpEmployee::with([
            'details',
            'sanitationCheck.sanitationArea.sanitationResult'
        ])->where('uuid', $uuid)->firstOrFail();

        $details = $report->details;
        $sanitation = $report->sanitationCheck;

        // Ambil sanitation area dan map hasil jam 1 & jam 2
        $sanitationAreas = $sanitation?->sanitationArea?->map(function ($area) {
            $results = $area->sanitationResult ?? collect();
            $area->results_by_hour = $results->keyBy('hour_to');
            return $area;
        }) ?? collect();

        return view('gmp_employee.editnext', compact('report', 'details', 'sanitation', 'sanitationAreas'))
            ->with('isEditNext', true);
    }

public function updateNext(Request $request, $uuid)
{
    DB::beginTransaction();

    try {
        $report = ReportGmpEmployee::where('uuid', $uuid)->firstOrFail();

        // 🧹 Hapus detail & followup lama (hindari dobel)
        $oldDetails = DetailGmpEmployee::where('report_uuid', $report->uuid)->get();
        foreach ($oldDetails as $oldDetail) {
            $oldDetail->followups()->delete();
            $oldDetail->delete();
        }

        // 🧩 Simpan detail baru (jam berikutnya)
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                $newDetail = DetailGmpEmployee::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detail['inspection_hour'] ?? null,
                    'section_name' => $detail['section_name'] ?? null,
                    'employee_name' => $detail['employee_name'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verification' => $detail['verification'] ?? null,
                ]);

                if (!empty($detail['followups'])) {
                    foreach ($detail['followups'] as $followup) {
                        $newDetail->followups()->create([
                            'notes' => $followup['notes'] ?? null,
                            'action' => $followup['action'] ?? null,
                            'verification' => $followup['verification'] ?? null,
                        ]);
                    }
                }
            }
        }

        // 🧼 Update data sanitasi (jam 1 & jam 2)
        $sanitationCheck = $report->sanitationCheck;
        if ($sanitationCheck) {
            $sanitationCheck->update([
                'hour_2' => $request->input('sanitation.hour_2'),
            ]);

            if ($request->has('sanitation_area')) {
                foreach ($request->sanitation_area as $areaInput) {
                    $area = $sanitationCheck->sanitationArea()
                        ->where('area_name', $areaInput['area_name'])
                        ->first();

                    if ($area) {
                        $area->update([
                            'chlorine_std' => $areaInput['chlorine_std'] ?? null,
                            'notes' => $areaInput['notes'] ?? null,
                            'corrective_action' => $areaInput['corrective_action'] ?? null,
                            'verification' => $areaInput['verification'] ?? null,
                        ]);

                        // ✅ Update atau buat hasil JAM 1
                        if (isset($areaInput['result'][1])) {
                            $result1 = $area->sanitationResult()->where('hour_to', 1)->first();
                            if ($result1) {
                                $result1->update([
                                    'chlorine_level' => $areaInput['result'][1]['chlorine_level'] ?? null,
                                    'temperature' => $areaInput['result'][1]['temperature'] ?? null,
                                ]);
                            } else {
                                $area->sanitationResult()->create([
                                    'hour_to' => 1,
                                    'chlorine_level' => $areaInput['result'][1]['chlorine_level'] ?? null,
                                    'temperature' => $areaInput['result'][1]['temperature'] ?? null,
                                ]);
                            }
                        }

                        // ✅ Update atau buat hasil JAM 2
                        if (isset($areaInput['result'][2])) {
                            $result2 = $area->sanitationResult()->where('hour_to', 2)->first();
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

                        // 🔁 Update follow-up jam ke-2
                        $area->followups()->delete();
                        if (!empty($areaInput['followups'])) {
                            foreach ($areaInput['followups'] as $followup) {
                                $area->followups()->create([
                                    'notes' => $followup['notes'] ?? null,
                                    'action' => $followup['action'] ?? null,
                                    'verification' => $followup['verification'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        DB::commit();
        return redirect()->route('gmp-employee.index')->with('success', 'Laporan jam berikutnya berhasil diperbarui.');
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
    }
}



}