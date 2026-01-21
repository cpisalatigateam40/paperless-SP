<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportEmulsionMaking;
use App\Models\HeaderEmulsionMaking;
use App\Models\DetailEmulsionMaking;
use App\Models\AgingEmulsionMaking;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportEmulsionMakingController extends Controller
{
    // public function index()
    // {
    //     $reports = ReportEmulsionMaking::with('header.details', 'header.agings')
    //     ->latest()
    //     ->paginate(10);

    //     $reports->getCollection()->transform(function ($report) {
    //         $ketidaksesuaian = 0;

    //         // 🔹 Cek dari tabel details
    //         if ($report->header && $report->header->details) {
    //             $ketidaksesuaian += $report->header->details
    //                 ->filter(fn($d) => $d->conformity === 'x')
    //                 ->count();
    //         }

    //         // 🔹 Cek dari tabel agings
    //         if ($report->header && $report->header->agings) {
    //             $ketidaksesuaian += $report->header->agings->filter(function ($a) {
    //                 return $a->sensory_color === 'x'
    //                     || $a->sensory_texture === 'x'
    //                     || $a->emulsion_result === 'Tidak OK';
    //             })->count();
    //         }

    //         $report->ketidaksesuaian = $ketidaksesuaian;

    //         return $report;
    //     });

    //     $rawMaterials = \App\Models\RawMaterial::all();
    //     return view('report_emulsion_makings.index', compact('reports', 'rawMaterials'));
    // }

    public function index(Request $request)
    {
        $query = ReportEmulsionMaking::with([
            'area',
            'header.details.rawMaterial',
            'header.details.premix',
            'header.agings'
        ])->latest();

        // 🔍 SEARCH GLOBAL
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 REPORT (HEADER UTAMA)
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 HEADER EMULSION
                $q->orWhereHas('header', function ($hq) use ($search) {
                    $hq->where('emulsion_type', 'like', "%{$search}%")
                    ->orWhere('production_code', 'like', "%{$search}%");
                });

                // 🔹 DETAIL EMULSION (RAW + PREMIX)
                $q->orWhereHas('header.details', function ($dq) use ($search) {
                $dq->where('weight', 'like', "%{$search}%")
                ->orWhere('temperature', 'like', "%{$search}%")
                ->orWhere('sensory', 'like', "%{$search}%")
                ->orWhere('conformity', 'like', "%{$search}%")

                ->orWhereHas('rawMaterial', function ($rm) use ($search) {
                    $rm->where('material_name', 'like', "%{$search}%");
                })

                ->orWhereHas('premix', function ($pm) use ($search) {
                    $pm->where('name', 'like', "%{$search}%");
                });
            });


                // 🔹 AGING
                $q->orWhereHas('header.agings', function ($aq) use ($search) {
                    $aq->where('emulsion_result', 'like', "%{$search}%")
                    ->orWhere('sensory_color', 'like', "%{$search}%")
                    ->orWhere('sensory_texture', 'like', "%{$search}%")
                    ->orWhere('temp_after', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $ketidaksesuaian = 0;

            if ($report->header) {

                // DETAIL
                $ketidaksesuaian += $report->header->details
                    ->filter(fn ($d) => $d->conformity === 'x')
                    ->count();

                // AGING
                $ketidaksesuaian += $report->header->agings
                    ->filter(fn ($a) =>
                        $a->sensory_color === 'x'
                        || $a->sensory_texture === 'x'
                        || $a->emulsion_result === 'Tidak OK'
                    )->count();
            }

            $report->ketidaksesuaian = $ketidaksesuaian;
            return $report;
        });

        $rawMaterials = \App\Models\RawMaterial::all();

        return view('report_emulsion_makings.index', compact('reports', 'rawMaterials'));
    }

    public function create()
    {
        $rawMaterials = \App\Models\RawMaterial::all();
        $premixes = \App\Models\Premix::orderBy('name')->get();
        $areas = \App\Models\Area::all();
        return view('report_emulsion_makings.create', compact('rawMaterials', 'premixes', 'areas'));
    }

    public function store(Request $request)
    {
        // 1. Buat report
        $report = new ReportEmulsionMaking();
        $report->uuid = Str::uuid();
        $report->area_uuid = Auth::user()->area_uuid;
        $report->date = $request->date;
        $report->shift = $request->shift;
        $report->created_by = Auth::user()->name;
        $report->save();

        // 2. Buat header
        $header = new HeaderEmulsionMaking();
        $header->uuid = Str::uuid();
        $header->report_uuid = $report->uuid;
        $header->emulsion_type = $request->emulsion_type;
        $header->production_code = $request->production_code;
        $header->save();

        // 3. Buat detail bahan baku
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
                if ($detail['material_type'] === 'raw') {
                    // Simpan ke FK lama
                    $detailModel = new DetailEmulsionMaking();
                    $detailModel->uuid = Str::uuid();
                    $detailModel->header_uuid = $header->uuid;
                    $detailModel->raw_material_uuid = $detail['material_uuid']; // FK tetap aman
                    $detailModel->weight = $detail['weight'];
                    $detailModel->temperature = $detail['temperature'];
                    $detailModel->conformity = $detail['conformity'];
                    $detailModel->save();
                } else {
                    // Simpan Premix di kolom baru
                    $detailModel = new DetailEmulsionMaking();
                    $detailModel->uuid = Str::uuid();
                    $detailModel->header_uuid = $header->uuid;
                    $detailModel->material_uuid = $detail['material_uuid'];
                    $detailModel->material_type = 'premix';
                    $detailModel->weight = $detail['weight'];
                    $detailModel->temperature = $detail['temperature'];
                    $detailModel->conformity = $detail['conformity'];
                    $detailModel->save();
                }
            }
        }

        // 4. Buat aging
        if ($request->has('agings')) {
            foreach ($request->agings as $aging) {
                $agingModel = new AgingEmulsionMaking();
                $agingModel->uuid = Str::uuid();
                $agingModel->header_uuid = $header->uuid;
                $agingModel->start_aging = $aging['start_aging'];
                $agingModel->finish_aging = $aging['finish_aging'];
                $agingModel->emulsion_result = $aging['emulsion_result'];
                $agingModel->sensory_color = $aging['sensory_color'];
                $agingModel->sensory_texture = $aging['sensory_texture'];
                $agingModel->temp_after = $aging['temp_after'];
                $agingModel->save();
            }
        }

        return redirect()->route('report_emulsion_makings.index')->with('success', 'Data berhasil disimpan');
    }

    public function destroy($uuid)
    {
        $report = ReportEmulsionMaking::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report_emulsion_makings.index')->with('success', 'Data berhasil dihapus');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportEmulsionMaking::where('uuid', $uuid)
            ->with('header', 'header.details', 'header.agings')
            ->firstOrFail();
        $rawMaterials = \App\Models\RawMaterial::all();
        $premixes = \App\Models\Premix::orderBy('name')->get();

        return view('report_emulsion_makings.add_detail', compact('report', 'rawMaterials', 'premixes'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportEmulsionMaking::where('uuid', $uuid)->with('header.agings')->firstOrFail();

        // Update header jika ada input
        if ($report->header) {
            $report->header->emulsion_type = $request->emulsion_type ?? $report->header->emulsion_type;
            $report->header->production_code = $request->production_code ?? $report->header->production_code;
            $report->header->save();
            $header = $report->header;
        } else {
            // Buat header baru kalau belum ada
            $header = new HeaderEmulsionMaking();
            $header->uuid = \Illuminate\Support\Str::uuid();
            $header->report_uuid = $report->uuid;
            $header->emulsion_type = $request->emulsion_type;
            $header->production_code = $request->production_code;
            $header->save();
        }

        // Hitung aging sudah ada berapa → otomatis tentukan aging index berikutnya
        $emulsiIndex = $header->agings->count() ?? 0;

        // Buat detail bahan baku tambahan
        if ($request->has('details')) {
            foreach ($request->details as $detail) {

                $detailModel = new DetailEmulsionMaking();
                $detailModel->uuid = \Illuminate\Support\Str::uuid();
                $detailModel->header_uuid = $header->uuid;
                $detailModel->weight = $detail['weight'];
                $detailModel->temperature = $detail['temperature'];
                $detailModel->conformity = $detail['conformity'];
                $detailModel->aging_index = $emulsiIndex; // tetap simpan aging index

                if (($detail['material_type'] ?? 'raw') === 'raw') {
                    // Simpan FK ke raw_material_uuid → validasi dulu
                    $exists = \App\Models\RawMaterial::where('uuid', $detail['material_uuid'])->exists();

                    if ($exists) {
                        $detailModel->raw_material_uuid = $detail['material_uuid'];
                    } else {
                        // kalau datanya bukan Raw Material, skip agar aman
                        continue;
                    }
                } else {
                    // Premix masuk ke kolom baru
                    $detailModel->material_uuid = $detail['material_uuid'];
                    $detailModel->material_type = 'premix';
                }

                $detailModel->save();
            }
        }

        // Buat aging baru juga (karena aging ke-n belum ada)
        if ($request->has('agings')) {
            foreach ($request->agings as $aging) {
                $agingModel = new AgingEmulsionMaking();
                $agingModel->uuid = \Illuminate\Support\Str::uuid();
                $agingModel->header_uuid = $header->uuid;
                $agingModel->start_aging = $aging['start_aging'];
                $agingModel->finish_aging = $aging['finish_aging'];
                $agingModel->emulsion_result = $aging['emulsion_result'];
                $agingModel->sensory_color = $aging['sensory_color'];
                $agingModel->sensory_texture = $aging['sensory_texture'];
                $agingModel->temp_after = $aging['temp_after'];
                $agingModel->save();
            }
        }

        return redirect()->route('report_emulsion_makings.index')->with('success', 'Detail berhasil ditambahkan');
    }


    public function approve($id)
    {
        $report = ReportEmulsionMaking::findOrFail($id);
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
        $report = ReportEmulsionMaking::findOrFail($id);
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
        $report = \App\Models\ReportEmulsionMaking::where('uuid', $uuid)
            ->with('header.agings', 'header.details.rawMaterial')
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

        $pdf = Pdf::loadView('report_emulsion_makings.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,

        ])
            ->setPaper('a4', 'landscape'); // landscape biar muat

        return $pdf->stream('report_emulsion_' . $report->uuid . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportEmulsionMaking::where('uuid', $uuid)->firstOrFail();
        $header = HeaderEmulsionMaking::where('report_uuid', $report->uuid)->first();
        $details = DetailEmulsionMaking::where('header_uuid', $header->uuid)->get();
        $agings = AgingEmulsionMaking::where('header_uuid', $header->uuid)->get();
        $rawMaterials = \App\Models\RawMaterial::all();
        $premixes = \App\Models\Premix::all();
        $areas = \App\Models\Area::all();

        return view('report_emulsion_makings.edit', compact(
            'report',
            'header',
            'details',
            'agings',
            'rawMaterials',
            'premixes',
            'areas'
        ));
    }

    public function update(Request $request, $uuid)
    {
        // Ambil report utama
        $report = ReportEmulsionMaking::where('uuid', $uuid)->firstOrFail();
        $header = HeaderEmulsionMaking::where('report_uuid', $report->uuid)->first();

        // Update report utama
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        // Update header
        $header->update([
            'emulsion_type' => $request->emulsion_type,
            'production_code' => $request->production_code,
        ]);

        // 🔹 Hapus detail lama, lalu simpan ulang (lebih sederhana & aman)
        DetailEmulsionMaking::where('header_uuid', $header->uuid)->delete();
        if ($request->has('details')) {
            foreach ($request->details as $detail) {
            $detailModel = new DetailEmulsionMaking();
            $detailModel->uuid = Str::uuid();
            $detailModel->header_uuid = $header->uuid;
            $detailModel->weight = $detail['weight'];
            $detailModel->temperature = $detail['temperature'];
            $detailModel->conformity = $detail['conformity'];
            $detailModel->aging_index = $detail['aging_index'] ?? 0;

            if (($detail['material_type'] ?? 'raw') === 'raw') {
                // FK ke raw_material_uuid
                $exists = \App\Models\RawMaterial::where('uuid', $detail['material_uuid'])->exists();
                if ($exists) {
                    $detailModel->raw_material_uuid = $detail['material_uuid'];
                }
            } else {
                // Premix di kolom baru
                $detailModel->material_uuid = $detail['material_uuid'];
                $detailModel->material_type = 'premix';
            }

            $detailModel->save();
        }
        }

        // 🔹 Hapus aging lama, lalu simpan ulang
        AgingEmulsionMaking::where('header_uuid', $header->uuid)->delete();
        if ($request->has('agings')) {
            foreach ($request->agings as $aging) {
                AgingEmulsionMaking::create([
                    'uuid' => Str::uuid(),
                    'header_uuid' => $header->uuid,
                    'start_aging' => $aging['start_aging'],
                    'finish_aging' => $aging['finish_aging'],
                    'emulsion_result' => $aging['emulsion_result'],
                    'sensory_color' => $aging['sensory_color'],
                    'sensory_texture' => $aging['sensory_texture'],
                    'temp_after' => $aging['temp_after'],
                ]);
            }
        }

        return redirect()->route('report_emulsion_makings.index')->with('success', 'Data berhasil diperbarui.');
    }

}