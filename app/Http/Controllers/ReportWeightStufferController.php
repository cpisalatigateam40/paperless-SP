<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportWeightStuffer;
use App\Models\DetailWeightStuffer;
use App\Models\TownsendStuffer;
use App\Models\HitechStuffer;
use App\Models\VemagStuffer;
use App\Models\Vemag2Stuffer;
use App\Models\HandtmannStuffer;
use App\Models\CaseStuffer;
use App\Models\WeightStuffer;
use App\Models\Product;
use App\Models\StandardStuffer;
use App\Models\WeightStufferMeasurement;
use App\Models\WeightStufferDocumentation;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\WeightStufferExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use Illuminate\Support\Facades\Storage;

class ReportWeightStufferController extends Controller
{
    use HasBulkApproval;
    protected string $bulkModel = ReportWeightStuffer::class;

    public function index(Request $request)
    {
        $query = ReportWeightStuffer::with([
            'area',
            'details.product',
            'details.townsend',
            'details.hitech',
            'details.vemag',
            'details.vemag2',
            'details.handtmann',
            'details.cases',
            'details.weights',
            'details.documentations',
        ])->latest();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL
                $q->orWhereHas('details', function ($d) use ($search) {

                    $d->where('production_code', 'like', "%{$search}%")
                    ->orWhere('time', 'like', "%{$search}%")
                    ->orWhere('weight_standard', 'like', "%{$search}%")
                    ->orWhere('long_standard', 'like', "%{$search}%")
                    ->orWhere('machine', 'like', "%{$search}%");

                    // 🔹 PRODUCT
                    $d->orWhereHas('product', function ($p) use ($search) {
                        $p->where('product_name', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%");
                    });

                    // 🔹 TOWNSEND
                    $d->orWhereHas('townsend', function ($t) use ($search) {
                        $t->where('stuffer_speed', 'like', "%{$search}%")
                        ->orWhere('trolley_total', 'like', "%{$search}%")
                        ->orWhere('avg_weight', 'like', "%{$search}%")
                        ->orWhere('avg_long', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                    });

                    // 🔹 HITECH
                    $d->orWhereHas('hitech', function ($h) use ($search) {
                        $h->where('stuffer_speed', 'like', "%{$search}%")
                        ->orWhere('trolley_total', 'like', "%{$search}%")
                        ->orWhere('avg_weight', 'like', "%{$search}%")
                        ->orWhere('avg_long', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                    });

                    // 🔹 CASE STUFFER
                    $d->orWhereHas('cases', function ($c) use ($search) {
                        $c->where('actual_case_1', 'like', "%{$search}%")
                        ->orWhere('actual_case_2', 'like', "%{$search}%");
                    });

                    // 🔹 WEIGHT MEASUREMENT
                    $d->orWhereHas('weights', function ($w) use ($search) {
                        $w->where('actual_weight', 'like', "%{$search}%")
                        ->orWhere('actual_long', 'like', "%{$search}%");
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('report_weight_stuffers.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $standards = StandardStuffer::with('product')->get();

        return view('report_weight_stuffers.create', compact('products', 'standards'));
    }

    public function store(Request $request)
    {
        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        $report = ReportWeightStuffer::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $key => $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'time' => $detail['time'],
                'machine'          => $detail['machine'] ?? null,
                'weight_standard' => $detail['weight_standard'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
                'fla_standard' => $detail['fla_standard'] ?? null,
                'gramase' => $detail['gramase'] ?? null,
                'weight_status' => $detail['weight_status'] ?? null,
                'weight_corrective_action' => $detail['weight_corrective_action'] ?? null,
                'weight_notes' => $detail['weight_notes'] ?? null,

                'long_status' => $detail['long_status'] ?? null,
                'long_corrective_action' => $detail['long_corrective_action'] ?? null,
                'long_notes' => $detail['long_notes'] ?? null,

                'fla_status' => $detail['fla_status'] ?? null,
                'fla_corrective_action' => $detail['fla_corrective_action'] ?? null,
                'fla_notes' => $detail['fla_notes'] ?? null,
            ]);

            // Upload dokumentasi
            if ($request->hasFile("details.{$key}.documentation")) {
                foreach ($request->file("details.{$key}.documentation") as $image) {
                    if (!$image->isValid()) continue;

                    $path = $image->store('weight-stuffer-documentation', 'public');

                    $detailModel->documentations()->create([
                        'uuid' => Str::uuid(),
                        'image' => $path,
                    ]);
                }
            }

            if ($detail['machine'] === 'townsend') {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'avg_fla' => $detail['avg_fla'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'hitech') {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'avg_fla' => $detail['avg_fla'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'vemag') {
                VemagStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'avg_fla' => $detail['avg_fla'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'vemag2') {
                Vemag2Stuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'avg_fla' => $detail['avg_fla'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'handtmann') {
                HandtmannStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'avg_fla' => $detail['avg_fla'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if (isset($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        // 'actual_case_1' => $case['actual_case_1'],
                        'actual_case_2' => $case['actual_case_2'],
                    ]);
                }
            }

            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {

                    // Cari index tertinggi dari semua tipe (weight, long, fla)
                    $maxIndex = 0;
                    foreach ($weightSet as $key => $value) {
                        if (preg_match('/^actual_(weight|long|fla)_(\d+)$/', $key, $m)) {
                            $maxIndex = max($maxIndex, (int) $m[2]);
                        }
                    }

                    // Loop dari 1 sampai index tertinggi
                    for ($i = 1; $i <= $maxIndex; $i++) {
                        $hasAnyValue =
                            isset($weightSet['actual_weight_' . $i]) ||
                            isset($weightSet['actual_long_'   . $i]) ||
                            isset($weightSet['actual_fla_'    . $i]);

                        if (!$hasAnyValue) continue; // skip baris kosong total

                        WeightStufferMeasurement::create([
                            'stuffer_id'    => $detailModel->id,
                            'actual_weight' => $weightSet['actual_weight_' . $i] ?? null,
                            'actual_long'   => $weightSet['actual_long_'   . $i] ?? null,
                            'actual_fla'    => $weightSet['actual_fla_'    . $i] ?? null,
                        ]);
                    }
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
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $standards = StandardStuffer::all();

        return view('report_weight_stuffers.add-detail', compact('report', 'products', 'standards'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();

        foreach ($request->details as $key => $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid'                     => Str::uuid(),
                'report_uuid'              => $report->uuid,
                'product_uuid'             => $detail['product_uuid'],
                'production_code'          => $detail['production_code'],
                'time'                     => $detail['time'],
                'machine'                  => $detail['machine'] ?? null,
                'gramase'                  => $detail['gramase'] ?? null,

                'weight_standard'          => $detail['weight_standard'] ?? null,
                'weight_status'            => $detail['weight_status'] ?? null,
                'weight_corrective_action' => $detail['weight_corrective_action'] ?? null,
                'weight_notes'             => $detail['weight_notes'] ?? null,

                'long_standard'            => $detail['long_standard'] ?? null,
                'long_status'              => $detail['long_status'] ?? null,
                'long_corrective_action'   => $detail['long_corrective_action'] ?? null,
                'long_notes'               => $detail['long_notes'] ?? null,

                'fla_standard'             => $detail['fla_standard'] ?? null,
                'fla_status'               => $detail['fla_status'] ?? null,
                'fla_corrective_action'    => $detail['fla_corrective_action'] ?? null,
                'fla_notes'                => $detail['fla_notes'] ?? null,
            ]);

            // Upload dokumentasi (kalau ada)
            if ($request->hasFile("details.{$key}.documentation")) {
                foreach ($request->file("details.{$key}.documentation") as $image) {
                    if (!$image->isValid()) continue;
                    $path = $image->store('weight-stuffer-documentation', 'public');
                    $detailModel->documentations()->create([
                        'uuid'  => Str::uuid(),
                        'image' => $path,
                    ]);
                }
            }

            // Machine-specific table (avg + notes + stuffer_speed)
            $machineData = [
                'detail_uuid'   => $detailModel->uuid,
                'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                'avg_weight'    => $detail['avg_weight'] ?? null,
                'avg_long'      => $detail['avg_long'] ?? null,
                'avg_fla'       => $detail['avg_fla'] ?? null,
                'notes'         => $detail['notes'] ?? null,
            ];

            match ($detail['machine'] ?? null) {
                'townsend'  => TownsendStuffer::create($machineData),
                'hitech'    => HitechStuffer::create($machineData),
                'vemag'     => VemagStuffer::create($machineData),
                'vemag2'    => Vemag2Stuffer::create($machineData),
                'handtmann' => HandtmannStuffer::create($machineData),
                default     => null,
            };

            // Casing
            if (!empty($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id'    => $detailModel->id,
                        'actual_case_2' => $case['actual_case_2'] ?? null,
                    ]);
                }
            }

            // Weights — scan maxIndex dari semua tipe
            if (!empty($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    $maxIndex = 0;
                    foreach ($weightSet as $k => $v) {
                        if (preg_match('/^actual_(weight|long|fla)_(\d+)$/', $k, $m)) {
                            $maxIndex = max($maxIndex, (int) $m[2]);
                        }
                    }

                    for ($i = 1; $i <= $maxIndex; $i++) {
                        $hasAny = isset($weightSet['actual_weight_' . $i])
                            || isset($weightSet['actual_long_'   . $i])
                            || isset($weightSet['actual_fla_'    . $i]);

                        if (!$hasAny) continue;

                        WeightStufferMeasurement::create([
                            'stuffer_id'    => $detailModel->id,
                            'actual_weight' => $weightSet['actual_weight_' . $i] ?? null,
                            'actual_long'   => $weightSet['actual_long_'   . $i] ?? null,
                            'actual_fla'    => $weightSet['actual_fla_'    . $i] ?? null,
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('report_weight_stuffers.index')
            ->with('success', 'Detail berhasil ditambahkan.');
    }

    public function known($id)
    {
        $report = ReportWeightStuffer::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve($id)
    {
        $report = ReportWeightStuffer::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    // public function exportPdf($uuid, $detail_uuid)
    // {
    //     $report = ReportWeightStuffer::with([
    //         'details.product',
    //         'details.townsend',
    //         'details.hitech',
    //         'details.vemag',
    //         'details.vemag2',
    //         'details.handtmann',
    //         'details.cases',
    //         'details.weights',
    //         'details.documentations'
    //     ])->where('uuid', $uuid)->firstOrFail();

    //     // Filter hanya detail yang dipilih
    //     $report->setRelation(
    //         'details',
    //         $report->details->filter(fn($d) => $d->uuid === $detail_uuid)->values()
    //     );

    //     abort_if($report->details->isEmpty(), 404);

    //     $createdInfo     = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
    //     $createdQrImage  = QrCode::format('png')->size(150)->generate($createdInfo);
    //     $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

    //     $approvedInfo    = $report->approved_by
    //         ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
    //         : "Belum disetujui";
    //     $approvedQrImage  = QrCode::format('png')->size(150)->generate($approvedInfo);
    //     $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

    //     $knownInfo    = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
    //     $knownQrImage  = QrCode::format('png')->size(150)->generate($knownInfo);
    //     $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

    //     $pdf = Pdf::loadView('report_weight_stuffers.pdf', [
    //         'report'      => $report,
    //         'createdQr'   => $createdQrBase64,
    //         'approvedQr'  => $approvedQrBase64,
    //         'knownQr'     => $knownQrBase64,
    //     ])->setPaper('F4', 'portrait');

    //     $productName = $report->details->first()->product->product_name ?? 'produk';
    //     $filename    = 'laporan-stuffer-' . Str::slug($productName) . '.pdf';

    //     return $pdf->stream($filename);
    // }

public function exportPdf($uuid, $detail_uuid)
{
    $report = ReportWeightStuffer::with([
        'details.product',
        'details.townsend',
        'details.hitech',
        'details.vemag',
        'details.vemag2',
        'details.handtmann',
        'details.cases',
        'details.weights',
        'details.documentations'
    ])->where('uuid', $uuid)->firstOrFail();

    if ($detail_uuid === 'all') {
        // semua detail, tidak difilter
    } elseif (str_starts_with($detail_uuid, 'group:')) {
        // group: beberapa uuid dipisah koma
        $uuids = explode(',', substr($detail_uuid, 6));
        $report->setRelation(
            'details',
            $report->details->filter(fn($d) => in_array($d->uuid, $uuids))->values()
        );
    } else {
        // single uuid
        $report->setRelation(
            'details',
            $report->details->filter(fn($d) => $d->uuid === $detail_uuid)->values()
        );
    }

    abort_if($report->details->isEmpty(), 404);

    $createdInfo     = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
    $createdQrImage  = QrCode::format('png')->size(150)->generate($createdInfo);
    $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

    $approvedInfo    = $report->approved_by
        ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
        : "Belum disetujui";
    $approvedQrImage  = QrCode::format('png')->size(150)->generate($approvedInfo);
    $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

    $knownInfo     = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
    $knownQrImage  = QrCode::format('png')->size(150)->generate($knownInfo);
    $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);

    $pdf = Pdf::loadView('report_weight_stuffers.pdf', [
        'report'     => $report,
        'createdQr'  => $createdQrBase64,
        'approvedQr' => $approvedQrBase64,
        'knownQr'    => $knownQrBase64,
    ])->setPaper('F4', 'portrait');

    $filename = 'laporan-stuffer-' . Str::slug($report->details->first()->product->product_name ?? 'produk') . '.pdf';

    return $pdf->stream($filename);
}

    public function edit($uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)
            ->with([
                'details.product',
                'details.weights',
                'details.cases',
                'details.townsend',
                'details.hitech',
                'details.vemag',
                'details.vemag2',
                'details.handtmann',
                'details.documentations',
            ])
            ->firstOrFail();

        // Bangun stuffer map: detail_uuid => stuffer model
        $stufferMap = [];
        foreach ($report->details as $d) {
            $stufferMap[$d->uuid] = match($d->machine) {
                'townsend'  => $d->townsend,
                'hitech'    => $d->hitech,
                'vemag'     => $d->vemag,
                'vemag2'    => $d->vemag2,
                'handtmann' => $d->handtmann,
                default     => null,
            };
        }

        $products  = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $standards = StandardStuffer::all();

        return view('report_weight_stuffers.edit', compact(
            'report', 'stufferMap', 'products', 'standards'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)
            ->with(['details.documentations'])
            ->firstOrFail();

        $report->update([
            'date'  => $request->date,
            'shift' => $request->shift ?? $report->shift,
        ]);

        // Hapus semua data lama kecuali dokumentasi (dihandle terpisah)
        foreach ($report->details as $oldDetail) {
            TownsendStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            HitechStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            VemagStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            Vemag2Stuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            HandtmannStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            CaseStuffer::where('stuffer_id', $oldDetail->id)->delete();
            WeightStufferMeasurement::where('stuffer_id', $oldDetail->id)->delete();

            // Set detail_id = null agar docs tidak ikut terhapus cascade
            $oldDetail->documentations()->update(['detail_id' => null]);
            $oldDetail->delete();
        }

        // Buat detail baru
        foreach ($request->details as $key => $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid'                     => Str::uuid(),
                'report_uuid'              => $report->uuid,
                'product_uuid'             => $detail['product_uuid'],
                'production_code'          => $detail['production_code'],
                'time'                     => $detail['time'],
                'machine'                  => $detail['machine'] ?? null,
                'gramase'                  => $detail['gramase'] ?? null,
                'weight_standard'          => $detail['weight_standard'] ?? null,
                'weight_status'            => $detail['weight_status'] ?? null,
                'weight_corrective_action' => $detail['weight_corrective_action'] ?? null,
                'weight_notes'             => $detail['weight_notes'] ?? null,
                'long_standard'            => $detail['long_standard'] ?? null,
                'long_status'              => $detail['long_status'] ?? null,
                'long_corrective_action'   => $detail['long_corrective_action'] ?? null,
                'long_notes'               => $detail['long_notes'] ?? null,
                'fla_standard'             => $detail['fla_standard'] ?? null,
                'fla_status'               => $detail['fla_status'] ?? null,
                'fla_corrective_action'    => $detail['fla_corrective_action'] ?? null,
                'fla_notes'                => $detail['fla_notes'] ?? null,
            ]);

            // Machine-specific table
            $machineData = [
                'detail_uuid'   => $detailModel->uuid,
                'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                'avg_weight'    => $detail['avg_weight'] ?? null,
                'avg_long'      => $detail['avg_long'] ?? null,
                'avg_fla'       => $detail['avg_fla'] ?? null,
                'notes'         => $detail['notes'] ?? null,
            ];

            match ($detail['machine'] ?? '') {
                'townsend'  => TownsendStuffer::create($machineData),
                'hitech'    => HitechStuffer::create($machineData),
                'vemag'     => VemagStuffer::create($machineData),
                'vemag2'    => Vemag2Stuffer::create($machineData),
                'handtmann' => HandtmannStuffer::create($machineData),
                default     => null,
            };

            // Casing
            if (!empty($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id'    => $detailModel->id,
                        'actual_case_2' => $case['actual_case_2'] ?? null,
                    ]);
                }
            }

            // Weights — scan maxIndex dari semua tipe
            if (!empty($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    $maxIndex = 0;
                    foreach ($weightSet as $k => $v) {
                        if (preg_match('/^actual_(weight|long|fla)_(\d+)$/', $k, $m)) {
                            $maxIndex = max($maxIndex, (int) $m[2]);
                        }
                    }
                    for ($i = 1; $i <= $maxIndex; $i++) {
                        $hasAny = isset($weightSet['actual_weight_' . $i])
                            || isset($weightSet['actual_long_'   . $i])
                            || isset($weightSet['actual_fla_'    . $i]);
                        if (!$hasAny) continue;
                        WeightStufferMeasurement::create([
                            'stuffer_id'    => $detailModel->id,
                            'actual_weight' => $weightSet['actual_weight_' . $i] ?? null,
                            'actual_long'   => $weightSet['actual_long_'   . $i] ?? null,
                            'actual_fla'    => $weightSet['actual_fla_'    . $i] ?? null,
                        ]);
                    }
                }
            }

            // Re-attach docs yang di-keep
            $keepUuids = $detail['keep_docs'] ?? [];
            if (!empty($keepUuids)) {
                WeightStufferDocumentation::whereIn('uuid', $keepUuids)
                    ->update(['detail_id' => $detailModel->id]);
            }

            // Upload docs baru
            if ($request->hasFile("details.{$key}.documentation")) {
                foreach ($request->file("details.{$key}.documentation") as $image) {
                    if (!$image->isValid()) continue;
                    $path = $image->store('weight-stuffer-documentation', 'public');
                    $detailModel->documentations()->create([
                        'uuid'  => Str::uuid(),
                        'image' => $path,
                    ]);
                }
            }
        }

        // Docs yang tidak di-keep siapapun (detail_id masih null) → hapus file & record
        WeightStufferDocumentation::whereNull('detail_id')->each(function ($doc) {
            Storage::disk('public')->delete($doc->image);
            $doc->delete();
        });

        return redirect()
            ->route('report_weight_stuffers.index')
            ->with('success', 'Laporan berhasil diperbarui.');
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
            $periodLabel = $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y');
        }
    
        $reports = ReportWeightStuffer::with([
                'details.product',
                'details.townsend',
                'details.hitech',
                'details.vemag',
                'details.vemag2',
                'details.handtmann',
                'details.cases',
                'details.weights',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Weight_Stuffer_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new WeightStufferExport($reports, $periodLabel), $filename);
    }
}