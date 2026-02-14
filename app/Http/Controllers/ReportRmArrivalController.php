<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportRmArrival;
use App\Models\DetailRmArrival;
use App\Models\Area;
use App\Models\Section;
use App\Models\RawMaterial;
use App\Models\Premix;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRmArrivalController extends Controller
{

    public function index(Request $request)
    {
        $query = ReportRmArrival::with('area', 'details.rawMaterial', 'section')
            ->latest();

        // 🔥 FILTER SECTION
        if ($request->filled('section')) {
            $query->whereHas('section', function ($q) use ($request) {
                $q->where('section_name', $request->section);
            });
        }

        // 🔍 SEARCH GLOBAL (HEADER + DETAIL + RELASI)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // =====================
                // HEADER REPORT
                // =====================
                $q->where('date', 'like', "%{$search}%")
                ->orWhere('shift', 'like', "%{$search}%")
                ->orWhere('created_by', 'like', "%{$search}%")
                ->orWhere('known_by', 'like', "%{$search}%")
                ->orWhere('approved_by', 'like', "%{$search}%");

                // =====================
                // AREA
                // =====================
                $q->orWhereHas('area', function ($qa) use ($search) {
                    $qa->where('name', 'like', "%{$search}%");
                });

                // =====================
                // SECTION
                // =====================
                $q->orWhereHas('section', function ($qs) use ($search) {
                    $qs->where('section_name', 'like', "%{$search}%");
                });

                // =====================
                // DETAIL RM ARRIVAL
                // =====================
                $q->orWhereHas('details', function ($qd) use ($search) {

                    $qd->where('supplier', 'like', "%{$search}%")
                    ->orWhere('time', 'like', "%{$search}%")
                    ->orWhere('production_code', 'like', "%{$search}%")
                    ->orWhere('temperature', 'like', "%{$search}%")
                    ->orWhere('rm_condition', 'like', "%{$search}%")
                    ->orWhere('packaging_condition', 'like', "%{$search}%")
                    ->orWhere('sensory_appearance', 'like', "%{$search}%")
                    ->orWhere('sensory_aroma', 'like', "%{$search}%")
                    ->orWhere('sensory_color', 'like', "%{$search}%")
                    ->orWhere('contamination', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('corrective_action', 'like', "%{$search}%");
                });

                // =====================
                // RAW MATERIAL
                // =====================
                $q->orWhereHas('details.rawMaterial', function ($qr) use ($search) {
                    $qr->where('material_name', 'like', "%{$search}%");
                });

                // =====================
                // PREMIX
                // =====================
                $q->orWhereHas('details.premix', function ($qp) use ($search) {
                    $qp->where('name', 'like', "%{$search}%");
                });
            });
        }


        $reports = $query->paginate(10)->withQueryString();

        // hitung ketidaksesuaian
        $reports->getCollection()->transform(function ($report) {
            $report->ketidaksesuaian = $report->details->filter(function ($d) {
                return (
                    in_array('x', [
                        $d->packaging_condition,
                        $d->sensory_appearance,
                        $d->sensory_aroma,
                        $d->sensory_color,
                    ]) || $d->contamination === '✓'
                );
            })->count();

            return $report;
        });

        return view('report_rm_arrivals.index', compact('reports'));
    }
    public function create()
    {
        return view('report_rm_arrivals.create', [
            'areas' => Area::all(),
            'rawMaterials' => RawMaterial::all(),
            'premixes' => Premix::orderBy('name')->get(),
            'sections' => Section::whereIn('section_name', ['Seasoning', 'Chillroom'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $report = ReportRmArrival::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->input('details', []) as $detail) {

            $data = [
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,

                // kolom umum
                'material_uuid' => $detail['material_uuid'] ?? null,
                'material_type' => $detail['material_type'] ?? 'raw',

                'supplier' => isset($detail['supplier'])
                    ? implode(',', $detail['supplier'])
                    : null,

                'rm_condition' => $detail['rm_condition'],
                'production_code' => $detail['production_code'] ?? null,
                'time' => $detail['time'],
                'temperature' => $detail['temperature'],
                'packaging_condition' => $detail['packaging_condition'],
                'sensory_appearance' => $detail['sensory_appearance'],
                'sensory_aroma' => $detail['sensory_aroma'],
                'sensory_color' => $detail['sensory_color'],
                'contamination' => $detail['contamination'],
                'problem' => $detail['problem'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
            ];

            if (($detail['material_type'] ?? 'raw') === 'raw') {
                $data['raw_material_uuid'] = $detail['material_uuid'];
            }

            DetailRmArrival::create($data);
        }


        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Laporan kedatangan bahan baku berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportRmArrival::where('uuid', $uuid)->firstOrFail();

        DetailRmArrival::where('report_uuid', $report->uuid)->delete();

        $report->delete();

        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function addDetail($uuid)
    {
        $report = ReportRmArrival::with('details')->where('uuid', $uuid)->firstOrFail();
        $rawMaterials = RawMaterial::all();
        $premixes = Premix::orderBy('name')->get();

        return view('report_rm_arrivals.add_detail', compact('report', 'rawMaterials', 'premixes'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportRmArrival::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'details.*.material_uuid' => 'required|uuid',
            'details.*.material_type' => 'required|in:raw,premix',
            'details.*.production_code' => 'required|string',
            'details.*.time' => 'nullable',
            'details.*.temperature' => 'nullable|numeric',
            'details.*.packaging_condition' => 'nullable|string',
            // 'details.*.sensorial_condition' => 'nullable|string',
            'details.*.sensory_appearance' => 'nullable|string',
            'details.*.sensory_aroma' => 'nullable|string',
            'details.*.sensory_color' => 'nullable|string',
            'details.*.contamination' => 'nullable|string',
            'details.*.problem' => 'nullable|string',
            'details.*.corrective_action' => 'nullable|string',
        ]);

        foreach ($request->input('details', []) as $detail) {

            $data = [
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,

                // kolom umum
                'material_uuid' => $detail['material_uuid'] ?? null,
                'material_type' => $detail['material_type'] ?? 'raw',

                'supplier' => isset($detail['supplier'])
                    ? implode(',', $detail['supplier'])
                    : null,

                'rm_condition' => $detail['rm_condition'],
                'production_code' => $detail['production_code'] ?? null,
                'time' => $detail['time'],
                'temperature' => $detail['temperature'],
                'packaging_condition' => $detail['packaging_condition'],
                'sensory_appearance' => $detail['sensory_appearance'],
                'sensory_aroma' => $detail['sensory_aroma'],
                'sensory_color' => $detail['sensory_color'],
                'contamination' => $detail['contamination'],
                'problem' => $detail['problem'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
            ];

            if (($detail['material_type'] ?? 'raw') === 'raw') {
                $data['raw_material_uuid'] = $detail['material_uuid'];
            }

            DetailRmArrival::create($data);
        }

        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Pemeriksaan tambahan berhasil ditambahkan.');
    }

    public function known($id)
    {
        $report = ReportRmArrival::findOrFail($id);
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
        $report = ReportRmArrival::findOrFail($id);
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
        $report = ReportRmArrival::with([
            'area',
            'details.rawMaterial',
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report_rm_arrivals.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('laporan_rm_arrival_' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportRmArrival::where('uuid', $uuid)->with('details')->firstOrFail();

        return view('report_rm_arrivals.edit', [
            'report' => $report,
            'areas' => Area::all(),
            'rawMaterials' => RawMaterial::all(),
            'premixes' => Premix::orderBy('name')->get(),
            'sections' => Section::whereIn('section_name', ['Seasoning', 'Chillroom'])->get(),
        ]);
    }


    public function update(Request $request, $uuid)
    {
        // 1️⃣ Ambil data header
        $report = ReportRmArrival::where('uuid', $uuid)->firstOrFail();

        // 2️⃣ Update header
        $report->update([
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'updated_by' => Auth::user()->name,
        ]);

        // 3️⃣ Hapus semua detail lama
        DetailRmArrival::where('report_uuid', $report->uuid)->delete();

        foreach ($request->input('details', []) as $detail) {

            $data = [
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,

                // kolom baru
                'material_uuid' => $detail['material_uuid'],
                'material_type' => $detail['material_type'] ?? 'raw',

                'supplier' => isset($detail['supplier'])
                    ? implode(',', $detail['supplier'])
                    : null,

                'rm_condition' => $detail['rm_condition'],
                'production_code' => $detail['production_code'] ?? null,
                'time' => $detail['time'],
                'temperature' => $detail['temperature'],
                'packaging_condition' => $detail['packaging_condition'],
                'sensory_appearance' => $detail['sensory_appearance'],
                'sensory_aroma' => $detail['sensory_aroma'],
                'sensory_color' => $detail['sensory_color'],
                'contamination' => $detail['contamination'],
                'problem' => $detail['problem'] ?? null,
                'corrective_action' => $detail['corrective_action'] ?? null,
            ];

            /**
             * RAW → isi FK lama
             * PREMIX → biarkan NULL
             */
            if (($detail['material_type'] ?? 'raw') === 'raw') {
                $data['raw_material_uuid'] = $detail['material_uuid'];
            }

            DetailRmArrival::create($data);
        }

        // 5️⃣ Redirect dengan notifikasi sukses
        return redirect()->route('report_rm_arrivals.index')
            ->with('success', 'Laporan kedatangan bahan baku berhasil diperbarui.');
    }

    public function productionCodes(Request $request)
    {
        return DetailRmArrival::query()
            ->whereNotNull('production_code')
            ->where('production_code', 'like', '%' . $request->q . '%')
            ->select('production_code')
            ->distinct()
            ->limit(10)
            ->pluck('production_code');
    }



}