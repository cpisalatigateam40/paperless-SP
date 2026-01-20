<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportPackagingVerif;
use App\Models\DetailPackagingVerif;
use App\Models\ChecklistPackagingDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportPackagingVerifController extends Controller
{
    public function index()
    {
        $reports = ReportPackagingVerif::with('details.checklist')
        ->latest()
        ->paginate(10);

        foreach ($reports as $report) {
            $totalNonConform = 0;

            foreach ($report->details as $detail) {
                $check = $detail->checklist;

                if (!$check) continue;

                $fieldsToCheck = [
                    'sampling_result',
                    'verif_md',
                    'sealing_condition_1', 'sealing_condition_2', 'sealing_condition_3',
                    'sealing_condition_4', 'sealing_condition_5',
                    'sealing_vacuum_1', 'sealing_vacuum_2', 'sealing_vacuum_3',
                    'sealing_vacuum_4', 'sealing_vacuum_5',
                ];

                foreach ($fieldsToCheck as $field) {
                    if (isset($check->$field) && $check->$field === 'Tidak OK') {
                        $totalNonConform++;
                    }
                }
            }

            // simpan hasil ke properti tambahan
            $report->ketidaksesuaian = $totalNonConform;
        }

        return view('report_packaging_verifs.index', compact('reports'));
    }



    public function create()
    {
        $products = \App\Models\Product::all();
        $areas = \App\Models\Area::all();
        $sections = \App\Models\Section::all();

        return view('report_packaging_verifs.create', compact('products', 'areas', 'sections'));
    }

    public function store(Request $request)
    {
        $report = ReportPackagingVerif::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $index => $detail) {
            $uploadMd = null;
            $uploadQr = null;
            $uploadEd = null;
            $uploadMdMulti = [];

            // Upload MD
            if (isset($detail['upload_md']) && $detail['upload_md'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_md'];
                $filename = time() . '_md_' . $index . '_' . $file->getClientOriginalName();
                $uploadMd = $file->storeAs('upload_packaging', $filename, 'public');
            }

            // Upload QR
            if (isset($detail['upload_qr']) && $detail['upload_qr'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_qr'];
                $filename = time() . '_qr_' . $index . '_' . $file->getClientOriginalName();
                $uploadQr = $file->storeAs('upload_packaging', $filename, 'public');
            }

            // Upload ED
            if (isset($detail['upload_ed']) && $detail['upload_ed'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_ed'];
                $filename = time() . '_ed_' . $index . '_' . $file->getClientOriginalName();
                $uploadEd = $file->storeAs('upload_packaging', $filename, 'public');
            }

            // if (isset($detail['upload_md_multi']) && is_array($detail['upload_md_multi'])) {
            //     foreach ($detail['upload_md_multi'] as $fileIndex => $file) {
            //         if ($file instanceof \Illuminate\Http\UploadedFile) {
            //             $filename = time() . "_md_multi_{$index}_{$fileIndex}_" . $file->getClientOriginalName();
            //             $path = $file->storeAs('upload_packaging', $filename, 'public');
            //             $uploadMdMulti[] = $path;
            //         }
            //     }
            // }

            if (!empty($detail['upload_md_multi']) && is_array($detail['upload_md_multi'])) {

                $files = array_filter(
                    $detail['upload_md_multi'],
                    fn ($file) => $file instanceof \Illuminate\Http\UploadedFile
                );

                foreach ($files as $fileIndex => $file) {
                    $filename = time() . "_md_multi_{$index}_{$fileIndex}_" . $file->getClientOriginalName();
                    $path = $file->storeAs('upload_packaging', $filename, 'public');
                    $uploadMdMulti[] = $path;
                }
            }


            $detailModel = DetailPackagingVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                // 'production_code' => $detail['production_code'],
                // 'expired_date' => $detail['expired_date'],
                'upload_md' => $uploadMd,
                'upload_qr' => $uploadQr,
                'upload_ed' => $uploadEd,
                'upload_md_multi' => !empty($uploadMdMulti) ? json_encode($uploadMdMulti) : null,
            ]);

            $checklistData = [
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'standard_weight' => $detail['checklist']['standard_weight'],
                'standard_long_pcs' => $detail['checklist']['standard_long_pcs'],
                'actual_long_pcs_1' => $detail['checklist']['actual_long_pcs_1'],
                'actual_long_pcs_2' => $detail['checklist']['actual_long_pcs_2'],
                'actual_long_pcs_3' => $detail['checklist']['actual_long_pcs_3'],
                'actual_long_pcs_4' => $detail['checklist']['actual_long_pcs_4'],
                'actual_long_pcs_5' => $detail['checklist']['actual_long_pcs_5'],
                'avg_long_pcs' => $detail['checklist']['avg_long_pcs'],

                'standard_weight_pcs' => $detail['checklist']['standard_weight_pcs'],
                'actual_weight_pcs_1' => $detail['checklist']['actual_weight_pcs_1'],
                'actual_weight_pcs_2' => $detail['checklist']['actual_weight_pcs_2'],
                'actual_weight_pcs_3' => $detail['checklist']['actual_weight_pcs_3'],
                'actual_weight_pcs_4' => $detail['checklist']['actual_weight_pcs_4'],
                'actual_weight_pcs_5' => $detail['checklist']['actual_weight_pcs_5'],
                'avg_weight_pcs' => $detail['checklist']['avg_weight_pcs'],
                'avg_weight' => $detail['checklist']['avg_weight'],
                'verif_md' => $detail['checklist']['verif_md'],
                'notes' => $detail['checklist']['notes'],
                'sampling_amount' => $detail['checklist']['sampling_amount'],
                'unit' => $detail['checklist']['unit'],
                'sampling_result' => $detail['checklist']['sampling_result'],
            ];

            // ✅ Mapping radio In Cutting → hanya kolom _1 yang diisi, _2.._5 null
            $inCutting = $detail['checklist']['in_cutting'] ?? null;
            for ($i = 1; $i <= 5; $i++) {
                $checklistData['in_cutting_manual_' . $i] = ($i == 1 && $inCutting == 'Manual') ? 'OK' : null;
                $checklistData['in_cutting_machine_' . $i] = ($i == 1 && $inCutting == 'Mesin') ? 'OK' : null;
            }

            // ✅ Mapping radio Packaging → hanya kolom _1 yang diisi, _2.._5 null
            $packaging = $detail['checklist']['packaging'] ?? null;
            for ($i = 1; $i <= 5; $i++) {
                $checklistData['packaging_thermoformer_' . $i] = ($i == 1 && $packaging == 'Thermoformer') ? 'OK' : null;
                $checklistData['packaging_manual_' . $i] = ($i == 1 && $packaging == 'Manual') ? 'OK' : null;
            }

            // ✏ Loop sealing_condition & sealing_vacuum
            foreach (['sealing_condition', 'sealing_vacuum'] as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            // ✏ Loop content_per_pack & actual_weight
            foreach (['content_per_pack', 'actual_weight'] as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            ChecklistPackagingDetail::create($checklistData);
        }

        return redirect()->route('report_packaging_verifs.index')->with('success', 'Laporan berhasil disimpan.');
    }


    public function destroy($uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_packaging_verifs.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();
        $products = \App\Models\Product::all();

        return view('report_packaging_verifs.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details as $index => $detail) {
            $uploadMd = null;
            $uploadQr = null;
            $uploadEd = null;
            $uploadMdMulti = [];

            // Upload MD
            if (isset($detail['upload_md']) && $detail['upload_md'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_md'];
                $filename = time() . '_md_' . $index . '_' . $file->getClientOriginalName();
                $uploadMd = $file->storeAs('upload_packaging', $filename, 'public');
            }

            // Upload QR
            if (isset($detail['upload_qr']) && $detail['upload_qr'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_qr'];
                $filename = time() . '_qr_' . $index . '_' . $file->getClientOriginalName();
                $uploadQr = $file->storeAs('upload_packaging', $filename, 'public');
            }

            // Upload ED
            if (isset($detail['upload_ed']) && $detail['upload_ed'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['upload_ed'];
                $filename = time() . '_ed_' . $index . '_' . $file->getClientOriginalName();
                $uploadEd = $file->storeAs('upload_packaging', $filename, 'public');
            }

            if (!empty($detail['upload_md_multi']) && is_array($detail['upload_md_multi'])) {

                $files = array_filter(
                    $detail['upload_md_multi'],
                    fn ($file) => $file instanceof \Illuminate\Http\UploadedFile
                );

                foreach ($files as $fileIndex => $file) {
                    $filename = time() . "_md_multi_detail_{$index}_{$fileIndex}_" . $file->getClientOriginalName();
                    $path = $file->storeAs('upload_packaging', $filename, 'public');
                    $uploadMdMulti[] = $path;
                }
            }

            $detailModel = DetailPackagingVerif::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'upload_md' => $uploadMd,
                'upload_qr' => $uploadQr,
                'upload_ed' => $uploadEd,
                'upload_md_multi' => $uploadMdMulti ? json_encode(array_values($uploadMdMulti)) : null,
            ]);

            $checklistData = [
                'uuid' => Str::uuid(),
                'detail_uuid' => $detailModel->uuid,
                'standard_weight' => $detail['checklist']['standard_weight'],
                'standard_long_pcs' => $detail['checklist']['standard_long_pcs'],
                'actual_long_pcs_1' => $detail['checklist']['actual_long_pcs_1'],
                'actual_long_pcs_2' => $detail['checklist']['actual_long_pcs_2'],
                'actual_long_pcs_3' => $detail['checklist']['actual_long_pcs_3'],
                'actual_long_pcs_4' => $detail['checklist']['actual_long_pcs_4'],
                'actual_long_pcs_5' => $detail['checklist']['actual_long_pcs_5'],
                'avg_long_pcs' => $detail['checklist']['avg_long_pcs'],

                'standard_weight_pcs' => $detail['checklist']['standard_weight_pcs'],
                'actual_weight_pcs_1' => $detail['checklist']['actual_weight_pcs_1'],
                'actual_weight_pcs_2' => $detail['checklist']['actual_weight_pcs_2'],
                'actual_weight_pcs_3' => $detail['checklist']['actual_weight_pcs_3'],
                'actual_weight_pcs_4' => $detail['checklist']['actual_weight_pcs_4'],
                'actual_weight_pcs_5' => $detail['checklist']['actual_weight_pcs_5'],
                'avg_weight_pcs' => $detail['checklist']['avg_weight_pcs'],
                'avg_weight' => $detail['checklist']['avg_weight'],
                'verif_md' => $detail['checklist']['verif_md'],
                'notes' => $detail['checklist']['notes'],
                'sampling_amount' => $detail['checklist']['sampling_amount'],
                'unit' => $detail['checklist']['unit'],
                'sampling_result' => $detail['checklist']['sampling_result'],
            ];

            // ✏ Mapping radio In Cutting: hanya in_cutting_manual_1 atau in_cutting_machine_1 yg "OK", sisanya null
            $inCutting = $detail['checklist']['in_cutting'] ?? null;
            for ($i = 1; $i <= 5; $i++) {
                $checklistData['in_cutting_manual_' . $i] = ($i == 1 && $inCutting == 'Manual') ? 'OK' : null;
                $checklistData['in_cutting_machine_' . $i] = ($i == 1 && $inCutting == 'Mesin') ? 'OK' : null;
            }

            // ✏ Mapping radio Packaging: hanya packaging_thermoformer_1 atau packaging_manual_1 yg "OK", sisanya null
            $packaging = $detail['checklist']['packaging'] ?? null;
            for ($i = 1; $i <= 5; $i++) {
                $checklistData['packaging_thermoformer_' . $i] = ($i == 1 && $packaging == 'Thermoformer') ? 'OK' : null;
                $checklistData['packaging_manual_' . $i] = ($i == 1 && $packaging == 'Manual') ? 'OK' : null;
            }

            // ✏ Loop sealing_condition & sealing_vacuum
            foreach (['sealing_condition', 'sealing_vacuum'] as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            // ✏ Loop content_per_pack & actual_weight
            foreach (['content_per_pack', 'actual_weight'] as $field) {
                for ($i = 1; $i <= 5; $i++) {
                    $key = $field . '_' . $i;
                    $checklistData[$key] = $detail['checklist'][$key] ?? null;
                }
            }

            ChecklistPackagingDetail::create($checklistData);
        }

        return redirect()->route('report_packaging_verifs.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportPackagingVerif::findOrFail($id);
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
        $report = ReportPackagingVerif::findOrFail($id);
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
        $report = ReportPackagingVerif::with('details.checklist', 'details.product')->where('uuid', $uuid)->firstOrFail();

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

        $pdf = Pdf::loadView('report_packaging_verifs.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper([0, 0, 1200, 595]);

        return $pdf->stream('report-packaging-' . $report->date . '.pdf');
    }

public function edit($uuid)
{
    $report = ReportPackagingVerif::with(['details.checklist','details.product'])
                ->where('uuid', $uuid)
                ->firstOrFail();

    $products = \App\Models\Product::all();
    $areas = \App\Models\Area::all();
    $sections = \App\Models\Section::all();

    $details = $report->details; // <-- penting

    return view('report_packaging_verifs.edit', compact('report','details','products','areas','sections'));
}



public function update(Request $request, $uuid)
{
    $report = ReportPackagingVerif::where('uuid', $uuid)->firstOrFail();

    $report->update([
        'section_uuid' => $request->section_uuid,
        'date' => $request->date,
        'shift' => $request->shift,
        'updated_by' => Auth::user()->name,
    ]);

    // Hapus detail lama
    DetailPackagingVerif::where('report_uuid', $report->uuid)->delete();

    // Simpan ulang semua detail baru
    foreach ($request->details as $index => $detail) {
        $uploadMd = null;
        $uploadQr = null;
        $uploadEd = null;
        $uploadMdMulti = [];

        // Upload file jika ada file baru
        if (isset($detail['upload_md']) && $detail['upload_md'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $detail['upload_md'];
            $filename = time() . '_md_' . $index . '_' . $file->getClientOriginalName();
            $uploadMd = $file->storeAs('upload_packaging', $filename, 'public');
        } elseif (!empty($detail['old_upload_md'])) {
            $uploadMd = $detail['old_upload_md'];
        }

        if (isset($detail['upload_qr']) && $detail['upload_qr'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $detail['upload_qr'];
            $filename = time() . '_qr_' . $index . '_' . $file->getClientOriginalName();
            $uploadQr = $file->storeAs('upload_packaging', $filename, 'public');
        } elseif (!empty($detail['old_upload_qr'])) {
            $uploadQr = $detail['old_upload_qr'];
        }

        if (isset($detail['upload_ed']) && $detail['upload_ed'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $detail['upload_ed'];
            $filename = time() . '_ed_' . $index . '_' . $file->getClientOriginalName();
            $uploadEd = $file->storeAs('upload_packaging', $filename, 'public');
        } elseif (!empty($detail['old_upload_ed'])) {
            $uploadEd = $detail['old_upload_ed'];
        }

        if (!empty($detail['upload_md_multi'])) {
            foreach ($detail['upload_md_multi'] as $fileMulti) {
                if ($fileMulti instanceof \Illuminate\Http\UploadedFile) {
                    $filename = time() . '_md_multi_' . $index . '_' . $fileMulti->getClientOriginalName();
                    $uploadMdMulti[] = $fileMulti->storeAs('upload_packaging', $filename, 'public');
                }
            }
        }

        $detailModel = DetailPackagingVerif::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $detail['product_uuid'],
            'time' => $detail['time'],
            'upload_md' => $uploadMd,
            'upload_qr' => $uploadQr,
            'upload_ed' => $uploadEd,
            'upload_md_multi' => !empty($uploadMdMulti) ? json_encode($uploadMdMulti) : null,

        ]);

        // checklist seperti store()
        $checklistData = $detail['checklist'];
        $checklistData['uuid'] = Str::uuid();
        $checklistData['detail_uuid'] = $detailModel->uuid;

        // Mapping khusus radio
        $inCutting = $checklistData['in_cutting'] ?? null;
        for ($i = 1; $i <= 5; $i++) {
            $checklistData['in_cutting_manual_' . $i] = ($i == 1 && $inCutting == 'Manual') ? 'OK' : null;
            $checklistData['in_cutting_machine_' . $i] = ($i == 1 && $inCutting == 'Mesin') ? 'OK' : null;
        }

        $packaging = $checklistData['packaging'] ?? null;
        for ($i = 1; $i <= 5; $i++) {
            $checklistData['packaging_thermoformer_' . $i] = ($i == 1 && $packaging == 'Thermoformer') ? 'OK' : null;
            $checklistData['packaging_manual_' . $i] = ($i == 1 && $packaging == 'Manual') ? 'OK' : null;
        }

        ChecklistPackagingDetail::create($checklistData);
    }

    return redirect()->route('report_packaging_verifs.index')->with('success', 'Laporan berhasil diperbarui.');
}


}