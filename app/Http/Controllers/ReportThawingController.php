<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportThawing;
use App\Models\DetailThawing;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\ThawingExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportThawingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $reports = ReportThawing::with(['area','details.rawMaterial'])
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    // kolom report
                    $q->where('date', 'like', "%$search%")
                    ->orWhere('shift', 'like', "%$search%")
                    ->orWhere('created_by', 'like', "%$search%")
                    ->orWhere('known_by', 'like', "%$search%")
                    ->orWhere('approved_by', 'like', "%$search%");

                    // relasi area
                    $q->orWhereHas('area', function ($area) use ($search) {
                        $area->where('name', 'like', "%$search%");
                    });

                    // relasi detail thawing
                    $q->orWhereHas('details', function ($detail) use ($search) {

                        $detail->where('start_thawing_time', 'like', "%$search%")
                            ->orWhere('end_thawing_time', 'like', "%$search%")
                            ->orWhere('package_condition', 'like', "%$search%")
                            ->orWhere('production_code', 'like', "%$search%")
                            ->orWhere('qty', 'like', "%$search%")
                            ->orWhere('room_condition', 'like', "%$search%")
                            ->orWhere('inspection_time', 'like', "%$search%")
                            ->orWhere('room_temp', 'like', "%$search%")
                            ->orWhere('water_temp', 'like', "%$search%")
                            ->orWhere('product_temp', 'like', "%$search%")
                            ->orWhere('product_condition', 'like', "%$search%");
                    });

                    // relasi raw material
                    $q->orWhereHas('details.rawMaterial', function ($rm) use ($search) {
                        $rm->where('material_name', 'like', "%$search%");
                    });

                });

            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('report_thawings.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rawMaterials = RawMaterial::orderBy('material_name')->get();

        return view('report_thawings.create', compact('rawMaterials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required',
            'details' => 'required|array',

            'details.*.raw_material_uuid' => 'required|uuid',
            'details.*.start_thawing_time' => 'nullable',
            'details.*.end_thawing_time' => 'nullable',
        ]);

        DB::beginTransaction();

        try {

            $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($request->shift ?? 'NON-SHIFT');

            $report = ReportThawing::create([
                'uuid' => Str::uuid(),
                'area_uuid' => auth()->user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
                'created_by' => auth()->user()->name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
            ]);

            foreach ($request->details as $detail) {

                // skip kalau RM kosong
                if (empty($detail['raw_material_uuid'])) {
                    continue;
                }

                DetailThawing::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'raw_material_uuid' => $detail['raw_material_uuid'] ?? null,
                    'start_thawing_time' => $detail['start_thawing_time'] ?? null,
                    'end_thawing_time' => $detail['end_thawing_time'] ?? null,
                    'package_condition' => $detail['package_condition'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'qty' => $detail['qty'] ?? null,
                    'room_condition' => $detail['room_condition'] ?? null,
                    'inspection_time' => $detail['inspection_time'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'water_temp' => $detail['water_temp'] ?? null,
                    'product_temp' => $detail['product_temp'] ?? null,
                    'product_condition' => $detail['product_condition'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('report_thawings.index')
                ->with('success', 'Report thawing berhasil dibuat');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data');
        }
    }

    public function known($id)
    {
        $report = ReportThawing::findOrFail($id);
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
        $report = ReportThawing::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function destroy($uuid)
    {
        DB::beginTransaction();

        try {

            $report = ReportThawing::where('uuid', $uuid)->firstOrFail();

            // hapus detail dulu
            DetailThawing::where('report_uuid', $report->uuid)->delete();

            // hapus report
            $report->delete();

            DB::commit();

            return redirect()
                ->route('report_thawings.index')
                ->with('success', 'Report thawing berhasil dihapus');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->with('error', 'Gagal menghapus laporan');
        }
    }

    public function exportPdf($uuid)
    {
        $report = ReportThawing::with('details.rawMaterial')
            ->where('uuid',$uuid)
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

        $pdf = Pdf::loadView('report_thawings.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-thawing.pdf');
    }

    public function addDetail($uuid)
    {
        $report = ReportThawing::where('uuid', $uuid)->firstOrFail();

        $rawMaterials = RawMaterial::orderBy('material_name')->get();

        return view('report_thawings.create_detail', compact(
            'report',
            'rawMaterials'
        ));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $request->validate([
            'details' => 'required|array',
            'details.*.raw_material_uuid' => 'required|uuid',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->details as $detail) {

                if (empty($detail['raw_material_uuid'])) {
                    continue;
                }

                DetailThawing::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $uuid,

                    'raw_material_uuid' => $detail['raw_material_uuid'] ?? null,
                    'start_thawing_time' => $detail['start_thawing_time'] ?? null,
                    'end_thawing_time' => $detail['end_thawing_time'] ?? null,
                    'package_condition' => $detail['package_condition'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'qty' => $detail['qty'] ?? null,
                    'room_condition' => $detail['room_condition'] ?? null,
                    'inspection_time' => $detail['inspection_time'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'water_temp' => $detail['water_temp'] ?? null,
                    'product_temp' => $detail['product_temp'] ?? null,
                    'product_condition' => $detail['product_condition'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('report_thawings.index', $uuid)
                ->with('success','Detail berhasil ditambahkan');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error','Gagal menyimpan detail');
        }
    }

    public function edit($uuid)
    {
        $report = ReportThawing::with('details')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $rawMaterials = RawMaterial::orderBy('material_name')->get();

        return view('report_thawings.edit', compact(
            'report',
            'rawMaterials'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required',
            'details' => 'required|array',
            'details.*.raw_material_uuid' => 'required|uuid',
        ]);

        DB::beginTransaction();

        try {

            $report = ReportThawing::where('uuid', $uuid)->firstOrFail();

            $shift = auth()->user()->hasRole('QC Inspector')
                ? session('shift_number') . '-' . session('shift_group')
                : ($request->shift ?? 'NON-SHIFT');

            $report->update([
                'date' => $request->date,
                'shift' => $shift,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
            ]);

            // hapus semua detail lama
            DetailThawing::where('report_uuid', $report->uuid)->delete();

            // insert ulang
            foreach ($request->details as $detail) {

                if (empty($detail['raw_material_uuid'])) {
                    continue;
                }

                DetailThawing::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'raw_material_uuid' => $detail['raw_material_uuid'] ?? null,
                    'start_thawing_time' => $detail['start_thawing_time'] ?? null,
                    'end_thawing_time' => $detail['end_thawing_time'] ?? null,
                    'package_condition' => $detail['package_condition'] ?? null,
                    'production_code' => $detail['production_code'] ?? null,
                    'qty' => $detail['qty'] ?? null,
                    'room_condition' => $detail['room_condition'] ?? null,
                    'inspection_time' => $detail['inspection_time'] ?? null,
                    'room_temp' => $detail['room_temp'] ?? null,
                    'water_temp' => $detail['water_temp'] ?? null,
                    'product_temp' => $detail['product_temp'] ?? null,
                    'product_condition' => $detail['product_condition'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('report_thawings.index')
                ->with('success', 'Report thawing berhasil diperbarui');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data');
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
            $periodLabel = $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y');
        }
    
        $reports = ReportThawing::with(['details.rawMaterial', 'area'])
            ->when(auth()->user()->hasRole('QC Inspector'), fn($q) =>
                $q->where('area_uuid', auth()->user()->area_uuid)
            )
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();
    
        $filename = 'Pemeriksaan_Thawing_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new ThawingExport($reports, $periodLabel), $filename);
    }
}