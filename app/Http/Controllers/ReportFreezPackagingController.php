<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReportFreezPackaging;
use App\Models\DetailFreezPackaging;
use App\Models\DataFreezing;
use App\Models\DataCartoning;
use App\Models\Area;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\FreezPackagingExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentationFreezPackaging;

class ReportFreezPackagingController extends Controller
{
    use HasBulkApproval;
    protected string $bulkModel = ReportFreezPackaging::class;

    public function index(Request $request)
    {
        $search = $request->search;

        $reports = ReportFreezPackaging::with([
                'area',
                'details.product',
                'details.freezing.actualTemps',
                'details.kartoning',
                'details.documentations',
                'details.kartoningDocumentations',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {

                    /* ================= HEADER ================= */
                    $qq->where('date', 'like', "%{$search}%")
                    ->orWhere('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                    /* ================= AREA ================= */
                    $qq->orWhereHas('area', function ($qa) use ($search) {
                        $qa->where('name', 'like', "%{$search}%");
                    });

                    /* ================= DETAIL ================= */
                    $qq->orWhereHas('details', function ($qd) use ($search) {
                        $qd->where('start_time', 'like', "%{$search}%")
                        ->orWhere('end_time', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%")
                        ->orWhere('best_before', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('verif_after', 'like', "%{$search}%")

                        /* PRODUCT */
                        ->orWhereHas('product', function ($qp) use ($search) {
                            $qp->where('product_name', 'like', "%{$search}%")
                                ->orWhere('production_code', 'like', "%{$search}%");
                        })

                        /* FREEZING */
                        ->orWhereHas('freezing', function ($qf) use ($search) {
                            $qf->where('start_product_temp', 'like', "%{$search}%")
                                ->orWhere('end_product_temp', 'like', "%{$search}%")
                                ->orWhere('iqf_room_temp', 'like', "%{$search}%")
                                ->orWhere('iqf_suction_temp', 'like', "%{$search}%")
                                ->orWhere('freezing_time_display', 'like', "%{$search}%")
                                ->orWhere('freezing_time_actual', 'like', "%{$search}%")
                                ->orWhere('standard_temp', 'like', "%{$search}%");
                        })

                        /* CARTONING */
                        ->orWhereHas('kartoning', function ($qk) use ($search) {
                            $qk->where('carton_code', 'like', "%{$search}%")
                                ->orWhere('carton_condition', 'like', "%{$search}%")
                                ->orWhere('carton_weight_standard', 'like', "%{$search}%")
                                ->orWhere('carton_weight_actual', 'like', "%{$search}%")
                                ->orWhere('avg_weight', 'like', "%{$search}%")
                                ->orWhere('content_rtg', 'like', "%{$search}%");
                        });
                    });

                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /* ================= HITUNG KETIDAKSESUAIAN ================= */
        foreach ($reports as $report) {
            $count = 0;

            foreach ($report->details as $detail) {
                if ($detail->verif_after === 'x') {
                    $count++;
                    continue;
                }

                if (optional($detail->kartoning)->carton_condition === 'x') {
                    $count++;
                }
            }

            $report->ketidaksesuaian = $count;
        }

        return view('report_freez_packagings.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        return view('report_freez_packagings.create', compact('areas', 'products'));
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

            // Simpan header report
            $report = ReportFreezPackaging::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
                'created_by' => Auth::user()->name,
                'notes' => $request->notes,
            ]);

            // Simpan detail, freezing, dan kartoning
            foreach ($request->details as $key => $detail) {
                $detailModel = $report->details()->create([
                    'uuid' => Str::uuid(),
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'start_time' => $detail['start_time'] ?? null,
                    'end_time' => $detail['end_time'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verif_after' => $detail['verif_after'] ?? null,
                    'start_time' => $detail['start_time'] ?? null,
                    'gramase' => $detail['gramase'] ?? null,
                    'release_status' => $detail['release_status'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);

                if ($request->file("details.$key.documentation")) {

                    foreach ($request->file("details.$key.documentation") as $image) {

                        if (!$image->isValid()) {
                            continue;
                        }

                        $path = $image->store(
                            'freez-packaging-documentation',
                            'public'
                        );

                        $detailModel->documentations()->create([
                            'uuid' => Str::uuid(),
                            'image' => $path,
                        ]);
                    }
                }

                $freezing = $detailModel->freezing()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'start_product_temp' => $detail['freezing']['start_product_temp'] ?? null,
                    'standard_temp' => $detail['freezing']['standard_temp'] ?? null,
                    'iqf_room_temp' => $detail['freezing']['iqf_room_temp'] ?? null,
                    'iqf_suction_temp' => $detail['freezing']['iqf_suction_temp'] ?? null,
                    'freezing_time_display' => $detail['freezing']['freezing_time_display'] ?? null,
                    'freezing_time_actual' => $detail['freezing']['freezing_time_actual'] ?? null,
                    'iqf_machine' => $detail['freezing']['iqf_machine'] ?? null,
                    'machine_type' => $detail['freezing']['machine_type'] ?? null,
                    'notes' => $detail['freezing']['notes'] ?? null,
                ]);

                foreach (($detail['freezing']['actual_temps'] ?? []) as $temp) {

                    if ($temp === null || $temp === '') {
                        continue;
                    }

                    $freezing->actualTemps()->create([
                        'uuid' => Str::uuid(),
                        'actual_temp' => $temp,
                    ]);
                }

                

                $detailModel->kartoning()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'carton_code' => $detail['kartoning']['carton_code'] ?? null,
                    'content_bag' => $detail['kartoning']['content_bag'] ?? null,
                    'content_binded' => $detail['kartoning']['content_binded'] ?? null,
                    'carton_weight_standard' => $detail['kartoning']['carton_weight_standard'] ?? null,
                    'carton_weight_actual' => $detail['kartoning']['carton_weight_actual'] ?? null,
                    'weight_1' => $detail['kartoning']['weight_1'] ?? null,
                    'weight_2' => $detail['kartoning']['weight_2'] ?? null,
                    'weight_3' => $detail['kartoning']['weight_3'] ?? null,
                    'weight_4' => $detail['kartoning']['weight_4'] ?? null,
                    'weight_5' => $detail['kartoning']['weight_5'] ?? null,
                    'avg_weight' => $detail['kartoning']['avg_weight'] ?? null,
                    'content_rtg' => $detail['kartoning']['content_rtg'] ?? null,
                    'carton_condition' => $detail['kartoning']['carton_condition'] ?? null,
                    'label_condition' => $detail['kartoning']['label_condition'] ?? null,
                    'notes' => $detail['kartoning']['notes'] ?? null,
                ]);

                if ($request->hasFile("details.$key.kartoning_documentation")) {

                    foreach (
                        $request->file("details.$key.kartoning_documentation")
                        as $image
                    ) {

                        if (!$image->isValid()) {
                            continue;
                        }

                        $path = $image->store(
                            'kartoning-documentation',
                            'public'
                        );

                        $detailModel->kartoningDocumentations()->create([
                            'uuid' => Str::uuid(),
                            'image' => $path,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('report_freez_packagings.index')->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report_freez_packagings.index')->with('success', 'Data berhasil dihapus');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        return view('report_freez_packagings.add-detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportFreezPackaging::where('uuid', $uuid)->firstOrFail();
        $details = $request->input('details', []);

        foreach ($details as $key => $item) {
            $detail = new DetailFreezPackaging([
                'report_uuid' => $report->uuid,
                'product_uuid' => $item['product_uuid'],
                'production_code' => $item['production_code'],
                'best_before' => $item['best_before'],
                'start_time' => now()->setTimeFromTimeString($item['start_time']),
                'end_time' => now()->setTimeFromTimeString($item['end_time']),
                'corrective_action' => $item['corrective_action'],
                'verif_after' => $item['verif_after'],
                'gramase' => $item['gramase'] ?? null,
                'release_status' => $item['release_status'] ?? null,
                'notes' => $item['notes'] ?? null,
            ]);
            $detail->save();

            if ($request->hasFile("details.$key.documentation")) {

                foreach ($request->file("details.$key.documentation") as $image) {

                    if (!$image->isValid()) {
                        continue;
                    }

                    $path = $image->store(
                        'freez-packaging-documentation',
                        'public'
                    );

                    $detail->documentations()->create([
                        'uuid'  => Str::uuid(),
                        'image' => $path,
                    ]);
                }
            }

            $freezing = $detail->freezing()->create([
                'iqf_room_temp' => $item['freezing']['iqf_room_temp'] ?? null,
                'standard_temp' => $item['freezing']['standard_temp'] ?? null,
                'iqf_suction_temp' => $item['freezing']['iqf_suction_temp'] ?? null,
                'freezing_time_display' => $item['freezing']['freezing_time_display'] ?? null,
                'freezing_time_actual' => $item['freezing']['freezing_time_actual'] ?? null,
                'iqf_machine' => $item['freezing']['iqf_machine'] ?? null,
                'machine_type' => $item['freezing']['machine_type'] ?? null,
                'notes' => $item['freezing']['notes'] ?? null,
            ]);

            foreach (($item['freezing']['actual_temps'] ?? []) as $temp) {

                if ($temp === null || $temp === '') {
                    continue;
                }

                $freezing->actualTemps()->create([
                    'actual_temp' => $temp,
                ]);
            }

            $detail->kartoning()->create([
                // 'carton_code' => $item['kartoning']['carton_code'],
                'content_bag' => $item['kartoning']['content_bag'],
                'content_binded' => $item['kartoning']['content_binded'],
                'carton_weight_standard' => $item['kartoning']['carton_weight_standard'],
                // 'carton_weight_actual' => $item['kartoning']['carton_weight_actual'],
                'weight_1' => $item['kartoning']['weight_1'] ?? null,
                'weight_2' => $item['kartoning']['weight_2'] ?? null,
                'weight_3' => $item['kartoning']['weight_3'] ?? null,
                'weight_4' => $item['kartoning']['weight_4'] ?? null,
                'weight_5' => $item['kartoning']['weight_5'] ?? null,
                'avg_weight' => $item['kartoning']['avg_weight'] ?? null,
                'content_rtg' => $item['kartoning']['content_rtg'] ?? null,
                'carton_condition' => $item['kartoning']['carton_condition'] ?? null,
                'label_condition' => $item['kartoning']['label_condition'] ?? null,
                'notes' => $item['kartoning']['notes'] ?? null,
            ]);

            // Upload dokumentasi kartoning
            if ($request->hasFile("details.$key.kartoning_documentation")) {

                foreach ($request->file("details.$key.kartoning_documentation") as $image) {

                    if (!$image->isValid()) {
                        continue;
                    }

                    $path = $image->store(
                        'kartoning-documentation',
                        'public'
                    );

                    $detail->kartoningDocumentations()->create([
                        'uuid' => Str::uuid(),
                        'image' => $path,
                    ]);
                }
            }
        }

        return redirect()->route('report_freez_packagings.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportFreezPackaging::findOrFail($id);
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
        $report = ReportFreezPackaging::findOrFail($id);
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
        $report = ReportFreezPackaging::with([
            'area',
            'details.product',
            'details.freezing.actualTemps',
            'details.kartoning',
            'details.documentations',
            'details.kartoningDocumentations'
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

        $pdf = Pdf::loadView('report_freez_packagings.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
        ->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-pembekuan-kartoning.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportFreezPackaging::with([
            'details.freezing.actualTemps',
            'details.kartoning',
            'details.documentations',
            'details.kartoningDocumentations'
        ])->where('uuid', $uuid)->firstOrFail();

        $areas = Area::all();
        
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name, MAX(shelf_life) as shelf_life, MAX(created_at) as created_at')
        ->groupBy('product_name')
        ->get();

        // Mapping details beserta relasinya
        $details = $report->details->map(fn($d) => [
            'uuid' => $d->uuid,
            'product_uuid' => $d->product_uuid,
            'gramase' => $d->gramase ?? $d->product->nett_weight,
            'production_code' => $d->production_code,
            'best_before' => $d->best_before,
            'start_time' => $d->start_time,
            'end_time' => $d->end_time,
            'corrective_action' => $d->corrective_action,
            'release_status' => $d->release_status,
            'notes' => $d->notes,
            'verif_after' => $d->verif_after,
            'documentations' => $d->documentations
                ->map(fn ($doc) => [
                    'uuid' => $doc->uuid,
                    'image' => $doc->image,
                ])
                ->values()
                ->toArray(),
            'kartoning_documentations' => $d->kartoningDocumentations
                ->map(fn ($doc) => [
                    'uuid' => $doc->uuid,
                    'image' => $doc->image,
                ])
                ->values()
                ->toArray(),
            'freezing' => $d->freezing ? [
                'iqf_machine' => $d->freezing->iqf_machine,
                'machine_type' => $d->freezing->machine_type,
                'notes' => $d->freezing->notes,
                'start_product_temp' => $d->freezing->start_product_temp,
                'end_product_temp' => $d->freezing->end_product_temp,
                'actual_temps' => $d->freezing->actualTemps
                    ->pluck('actual_temp')
                    ->values()
                    ->toArray(),
                'standard_temp' => $d->freezing->standard_temp,
                'iqf_room_temp' => $d->freezing->iqf_room_temp,
                'iqf_suction_temp' => $d->freezing->iqf_suction_temp,
                'freezing_time_display' => $d->freezing->freezing_time_display,
                'freezing_time_actual' => $d->freezing->freezing_time_actual,
            ] : null,
            'kartoning' => $d->kartoning ? [
                'carton_condition' => $d->kartoning->carton_condition,
                'content_bag' => $d->kartoning->content_bag,
                'content_binded' => $d->kartoning->content_binded,
                'content_rtg' => $d->kartoning->content_rtg,
                'carton_weight_standard' => $d->kartoning->carton_weight_standard,
                'carton_weight_actual' => $d->kartoning->carton_weight_actual,
                'weight_1' => $d->kartoning->weight_1,
                'weight_2' => $d->kartoning->weight_2,
                'weight_3' => $d->kartoning->weight_3,
                'weight_4' => $d->kartoning->weight_4,
                'weight_5' => $d->kartoning->weight_5,
                'avg_weight' => $d->kartoning->avg_weight,
                'carton_code' => $d->kartoning->carton_code,
                'label_condition' => $d->kartoning->label_condition,
                'notes' => $d->kartoning->notes,
            ] : null,
        ])->values();

        return view('report_freez_packagings.edit', compact('report', 'areas', 'products', 'details'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportFreezPackaging::with([
            'details.documentations',
            'details.freezing.actualTemps',
            'details.kartoning'
        ])->where('uuid', $uuid)->firstOrFail();

        DB::beginTransaction();

        try {

            $report->update([
                'date'  => $request->date,
                'shift' => $request->shift,
                'notes' => $request->notes,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Simpan dokumentasi lama
            |--------------------------------------------------------------------------
            */
            $oldDocumentations = [];
            $oldKartoningDocumentations = [];

            foreach ($report->details as $oldDetail) {

                $oldDocumentations[$oldDetail->uuid] =
                    $oldDetail->documentations->map(function ($doc) {
                        return [
                            'uuid'  => $doc->uuid,
                            'image' => $doc->image,
                        ];
                    })->toArray();

                $oldKartoningDocumentations[$oldDetail->uuid] =
                    $oldDetail->kartoningDocumentations
                        ->map(function ($doc) {
                            return [
                                'uuid'  => $doc->uuid,
                                'image' => $doc->image,
                            ];
                        })->toArray();

                if ($oldDetail->freezing) {
                    $oldDetail->freezing->actualTemps()->delete();
                    $oldDetail->freezing()->delete();
                }

                $oldDetail->kartoning()->delete();
            }

            // hapus setelah backup selesai
            foreach ($report->details as $oldDetail) {
                $oldDetail->documentations()->delete();
                $oldDetail->kartoningDocumentations()->delete();
            }

            $report->details()->delete();

            /*
            |--------------------------------------------------------------------------
            | Simpan detail baru
            |--------------------------------------------------------------------------
            */
            foreach ($request->details as $key => $detail) {

                $oldDetailUuid = $detail['uuid'] ?? null;

                $detailModel = $report->details()->create([
                    'uuid'              => Str::uuid(),
                    'product_uuid'      => $detail['product_uuid'],
                    'production_code'   => $detail['production_code'],
                    'best_before'       => $detail['best_before'],
                    'start_time'        => $detail['start_time'] ?? null,
                    'end_time'          => $detail['end_time'] ?? null,
                    'corrective_action' => $detail['corrective_action'] ?? null,
                    'verif_after'       => $detail['verif_after'] ?? null,
                    'gramase'           => $detail['gramase'] ?? null,
                    'release_status'    => $detail['release_status'] ?? null,
                    'notes'             => $detail['notes'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Restore dokumentasi lama
                |--------------------------------------------------------------------------
                */
                if (
                    $oldDetailUuid &&
                    isset($oldDocumentations[$oldDetailUuid])
                ) {

                    $deletedDocs = $detail['delete_documentations'] ?? [];

                    foreach ($oldDocumentations[$oldDetailUuid] as $doc) {

                        if (in_array($doc['uuid'], $deletedDocs)) {

                            if (
                                !empty($doc['image']) &&
                                Storage::disk('public')->exists($doc['image'])
                            ) {
                                Storage::disk('public')->delete($doc['image']);
                            }

                            continue;
                        }

                        $detailModel->documentations()->create([
                            'uuid'  => Str::uuid(),
                            'image' => $doc['image'],
                        ]);
                    }
                }

                if (
                    $oldDetailUuid &&
                    isset($oldKartoningDocumentations[$oldDetailUuid])
                ) {

                    $deletedDocs =
                        $detail['delete_kartoning_documentations'] ?? [];

                    foreach (
                        $oldKartoningDocumentations[$oldDetailUuid]
                        as $doc
                    ) {

                        if (in_array($doc['uuid'], $deletedDocs)) {

                            if (
                                !empty($doc['image']) &&
                                Storage::disk('public')->exists($doc['image'])
                            ) {
                                Storage::disk('public')->delete(
                                    $doc['image']
                                );
                            }

                            continue;
                        }

                        $detailModel
                            ->kartoningDocumentations()
                            ->create([
                                'uuid' => Str::uuid(),
                                'image' => $doc['image'],
                            ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Upload dokumentasi baru
                |--------------------------------------------------------------------------
                */
                if ($request->hasFile("details.$key.documentation")) {

                    foreach ($request->file("details.$key.documentation") as $image) {

                        if (!$image->isValid()) {
                            continue;
                        }

                        $path = $image->store(
                            'freez-packaging-documentation',
                            'public'
                        );

                        $detailModel->documentations()->create([
                            'uuid'  => Str::uuid(),
                            'image' => $path,
                        ]);
                    }
                }

                if ($request->hasFile(
                    "details.$key.kartoning_documentation"
                )) {

                    foreach (
                        $request->file(
                            "details.$key.kartoning_documentation"
                        ) as $image
                    ) {

                        if (!$image->isValid()) {
                            continue;
                        }

                        $path = $image->store(
                            'kartoning-documentation',
                            'public'
                        );

                        $detailModel
                            ->kartoningDocumentations()
                            ->create([
                                'uuid' => Str::uuid(),
                                'image' => $path,
                            ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Freezing
                |--------------------------------------------------------------------------
                */
                $freezing = $detailModel->freezing()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'start_product_temp' => $detail['freezing']['start_product_temp'] ?? null,
                    'standard_temp' => $detail['freezing']['standard_temp'] ?? null,
                    'iqf_room_temp' => $detail['freezing']['iqf_room_temp'] ?? null,
                    'iqf_suction_temp' => $detail['freezing']['iqf_suction_temp'] ?? null,
                    'freezing_time_display' => $detail['freezing']['freezing_time_display'] ?? null,
                    'freezing_time_actual' => $detail['freezing']['freezing_time_actual'] ?? null,
                    'iqf_machine' => $detail['freezing']['iqf_machine'] ?? null,
                    'machine_type' => $detail['freezing']['machine_type'] ?? null,
                    'notes' => $detail['freezing']['notes'] ?? null,
                ]);

                foreach (($detail['freezing']['actual_temps'] ?? []) as $temp) {

                    if ($temp === null || $temp === '') {
                        continue;
                    }

                    $freezing->actualTemps()->create([
                        'uuid' => Str::uuid(),
                        'actual_temp' => $temp,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Kartoning
                |--------------------------------------------------------------------------
                */
                $detailModel->kartoning()->create([
                    'uuid' => Str::uuid(),
                    'detail_uuid' => $detailModel->uuid,
                    'carton_code' => $detail['kartoning']['carton_code'] ?? null,
                    'content_bag' => $detail['kartoning']['content_bag'] ?? null,
                    'content_binded' => $detail['kartoning']['content_binded'] ?? null,
                    'carton_weight_standard' => $detail['kartoning']['carton_weight_standard'] ?? null,
                    'carton_weight_actual' => $detail['kartoning']['carton_weight_actual'] ?? null,
                    'weight_1' => $detail['kartoning']['weight_1'] ?? null,
                    'weight_2' => $detail['kartoning']['weight_2'] ?? null,
                    'weight_3' => $detail['kartoning']['weight_3'] ?? null,
                    'weight_4' => $detail['kartoning']['weight_4'] ?? null,
                    'weight_5' => $detail['kartoning']['weight_5'] ?? null,
                    'avg_weight' => $detail['kartoning']['avg_weight'] ?? null,
                    'content_rtg' => $detail['kartoning']['content_rtg'] ?? null,
                    'carton_condition' => $detail['kartoning']['carton_condition'] ?? null,
                    'label_condition' => $detail['kartoning']['label_condition'] ?? null,
                    'notes' => $detail['kartoning']['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('report_freez_packagings.index')
                ->with('success', 'Data berhasil diperbarui');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Gagal memperbarui data: ' . $e->getMessage()
            );
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:range,month',
            'date_from'   => 'required_if:filter_type,range|nullable|date',
            'date_to'     => 'required_if:filter_type,range|nullable|date|after_or_equal:date_from',
            'month'       => 'required_if:filter_type,month|nullable|date_format:Y-m',
        ]);

        if ($request->filter_type === 'month') {
            $dateFrom    = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $dateTo      = $dateFrom->copy()->endOfMonth();
            $periodLabel = $dateFrom->translatedFormat('F Y');
        } else {
            $dateFrom    = Carbon::parse($request->date_from)->startOfDay();
            $dateTo      = Carbon::parse($request->date_to)->endOfDay();
            $periodLabel = $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y');
        }

        $reports = ReportFreezPackaging::with([
                'details.product',
                'details.freezing.actualTemps',
                'details.kartoning',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString()
            ])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'Freez_Packaging_' .
            $dateFrom->format('Ymd') . '_' .
            $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(
            new FreezPackagingExport($reports, $periodLabel),
            $filename
        );
    }
}