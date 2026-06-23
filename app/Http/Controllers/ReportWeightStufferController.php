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
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\WeightStufferExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;

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

        foreach ($request->details as $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'production_code' => $detail['production_code'],
                'time' => $detail['time'],
                'weight_standard' => $detail['weight_standard'] ?? null,
                'long_standard' => $detail['long_standard'] ?? null,
                'gramase' => $detail['gramase'] ?? null,
            ]);

            if ($detail['machine'] === 'townsend') {
                TownsendStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'hitech') {
                HitechStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'vemag') {
                VemagStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'vemag2') {
                Vemag2Stuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                ]);
            }

            if ($detail['machine'] === 'handtmann') {
                HandtmannStuffer::create([
                    'detail_uuid' => $detailModel->uuid,
                    'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                    'avg_weight' => $detail['avg_weight'] ?? null,
                    'avg_long' => $detail['avg_long'] ?? null,
                    'notes' => $detail['notes'] ?? null,
                ]);
            }

            // Casing
            if (!empty($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id' => $detailModel->id,
                        // 'actual_case_1' => $case['actual_case_1'] ?? null,
                        'actual_case_2' => $case['actual_case_2'] ?? null,
                    ]);
                }
            }

            // Berat & Panjang Aktual
            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    // loop setiap pasangan weight/long
                    foreach ($weightSet as $key => $value) {
                        // cek kalau key diawali actual_weight_x
                        if (strpos($key, 'actual_weight_') === 0) {
                            $index = str_replace('actual_weight_', '', $key);
                            WeightStufferMeasurement::create([
                                'stuffer_id' => $detailModel->id,
                                'actual_weight' => $value ?? null,
                                'actual_long' => $weightSet['actual_long_' . $index] ?? null,
                            ]);
                        }
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

    public function exportPdf($uuid)
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

        $pdf = Pdf::loadView('report_weight_stuffers.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('F4', 'portrait');
        return $pdf->stream('laporan-verifikasi-berat-stuffer.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)
            ->with([
                'details.product',
                'details.townsend',
                'details.hitech',
                'details.vemag',
                'details.vemag2',
                'details.handtmann',
                'details.cases',
                'details.weights',
                'details.documentations',
            ])->firstOrFail();

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name, MAX(shelf_life) as shelf_life, MAX(created_at) as created_at')
        ->groupBy('product_name')
        ->get();
        $standards = StandardStuffer::with('product')->get();


        // di controller edit()
        $detailsJson = $report->details->map(function ($d) {
            if ($d->townsend)      { $mt = 'townsend';  $md = $d->townsend; }
            elseif ($d->hitech)    { $mt = 'hitech';    $md = $d->hitech; }
            elseif ($d->vemag)     { $mt = 'vemag';     $md = $d->vemag; }
            elseif ($d->vemag2)    { $mt = 'vemag2';    $md = $d->vemag2; }
            elseif ($d->handtmann) { $mt = 'handtmann'; $md = $d->handtmann; }
            else                   { $mt = '';           $md = null; }

            return [
                'machine'                  => $mt,
                'time'                     => $d->time,
                'cases_actual_case_2'      => $d->cases->first()->actual_case_2 ?? null,
                'weight_standard'          => $d->weight_standard,
                'long_standard'            => $d->long_standard,
                'fla_standard'             => $d->fla_standard,
                'weight_status'            => $d->weight_status,
                'weight_corrective_action' => $d->weight_corrective_action,
                'weight_notes'             => $d->weight_notes,
                'long_status'              => $d->long_status,
                'long_corrective_action'   => $d->long_corrective_action,
                'long_notes'               => $d->long_notes,
                'fla_status'               => $d->fla_status,
                'fla_corrective_action'    => $d->fla_corrective_action,
                'fla_notes'                => $d->fla_notes,
                'stuffer_speed'            => $md->stuffer_speed ?? null,
                'avg_weight'               => $md->avg_weight ?? null,
                'avg_long'                 => $md->avg_long ?? null,
                'avg_fla'                  => $md->avg_fla ?? null,
                'notes'                    => $md->notes ?? null,
                'weights'                  => $d->weights->map(fn($w) => [
                    'actual_weight' => $w->actual_weight,
                    'actual_long'   => $w->actual_long,
                    'actual_fla'    => $w->actual_fla,
                ])->values()->toArray(),
                'documentations' => $d->documentations->map(function ($doc) {
                    return [
                        'uuid' => $doc->uuid,
                        'image' => $doc->image,
                        'url'   => asset('storage/' . $doc->image),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();


        return view('report_weight_stuffers.edit', compact('report', 'products', 'standards', 'detailsJson'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportWeightStuffer::where('uuid', $uuid)->firstOrFail();

        $report->update([
            'date'  => $request->date,
            'shift' => $request->shift,
        ]);

        foreach ($report->details as $oldDetail) {
            TownsendStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            HitechStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            VemagStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            Vemag2Stuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            HandtmannStuffer::where('detail_uuid', $oldDetail->uuid)->delete();
            CaseStuffer::where('stuffer_id', $oldDetail->id)->delete();
            WeightStufferMeasurement::where('stuffer_id', $oldDetail->id)->delete();
            $oldDetail->delete();
        }

        foreach ($request->details as $detail) {
            $detailModel = DetailWeightStuffer::create([
                'uuid'                     => Str::uuid(),
                'report_uuid'              => $report->uuid,
                'product_uuid'             => $detail['product_uuid'],
                'production_code'          => $detail['production_code'],
                'time'                     => $detail['time'],
                'machine'                  => $detail['machine'] ?? null,
                'weight_standard'          => $detail['weight_standard'] ?? null,
                'long_standard'            => $detail['long_standard'] ?? null,
                'fla_standard'             => $detail['fla_standard'] ?? null,
                'gramase'                  => $detail['gramase'] ?? null,
                'weight_status'            => $detail['weight_status'] ?? null,
                'weight_corrective_action' => $detail['weight_corrective_action'] ?? null,
                'weight_notes'             => $detail['weight_notes'] ?? null,
                'long_status'              => $detail['long_status'] ?? null,
                'long_corrective_action'   => $detail['long_corrective_action'] ?? null,
                'long_notes'               => $detail['long_notes'] ?? null,
                'fla_status'               => $detail['fla_status'] ?? null,
                'fla_corrective_action'    => $detail['fla_corrective_action'] ?? null,
                'fla_notes'                => $detail['fla_notes'] ?? null,
            ]);

            $machineData = [
                'detail_uuid'  => $detailModel->uuid,
                'stuffer_speed' => $detail['stuffer_speed'] ?? null,
                'avg_weight'   => $detail['avg_weight'] ?? null,
                'avg_long'     => $detail['avg_long'] ?? null,
                'avg_fla'      => $detail['avg_fla'] ?? null,
                'notes'        => $detail['notes'] ?? null,
            ];

            match ($detail['machine'] ?? '') {
                'townsend'  => TownsendStuffer::create($machineData),
                'hitech'    => HitechStuffer::create($machineData),
                'vemag'     => VemagStuffer::create($machineData),
                'vemag2'    => Vemag2Stuffer::create($machineData),
                'handtmann' => HandtmannStuffer::create($machineData),
                default     => null,
            };

            if (isset($detail['cases'])) {
                foreach ($detail['cases'] as $case) {
                    CaseStuffer::create([
                        'stuffer_id'    => $detailModel->id,
                        'actual_case_2' => $case['actual_case_2'],
                    ]);
                }
            }

            if (isset($detail['weights'])) {
                foreach ($detail['weights'] as $weightSet) {
                    $maxIndex = 0;
                    foreach ($weightSet as $key => $value) {
                        if (preg_match('/^actual_(weight|long|fla)_(\d+)$/', $key, $m)) {
                            $maxIndex = max($maxIndex, (int) $m[2]);
                        }
                    }

                    for ($i = 1; $i <= $maxIndex; $i++) {
                        $hasAnyValue =
                            isset($weightSet['actual_weight_' . $i]) ||
                            isset($weightSet['actual_long_'   . $i]) ||
                            isset($weightSet['actual_fla_'    . $i]);

                        if (!$hasAnyValue) continue;

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

        return redirect()->route('report_weight_stuffers.index')->with('success', 'Laporan berhasil diperbarui.');
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