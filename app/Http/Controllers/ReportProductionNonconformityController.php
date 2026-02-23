<?php

namespace App\Http\Controllers;

use App\Models\ReportProductionNonconformity;
use App\Models\DetailProductionNonconformity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

class ReportProductionNonconformityController extends Controller
{
    // public function index()
    // {
    //     $reports = ReportProductionNonconformity::with('details')->latest()->paginate(10);
    //     return view('report_production_nonconformities.index', compact('reports'));
    // }
    public function index(Request $request)
    {
        $reports = ReportProductionNonconformity::with([
                'area',
                'details'
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

                    // ===== DETAIL NONCONFORMITY =====
                    $qq->orWhereHas('details', function ($d) use ($search) {
                        $d->where('occurrence_time', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('quantity', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%")
                        ->orWhere('hazard_category', 'like', "%{$search}%")
                        ->orWhere('disposition', 'like', "%{$search}%")
                        ->orWhere('remark', 'like', "%{$search}%")
                        ->orWhere('evidence', 'like', "%{$search}%");
                    });
                });
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('report_production_nonconformities.index', compact('reports'));
    }


    public function create()
    {
        return view('report_production_nonconformities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'details' => 'required|array',
            'details.*.occurrence_time' => 'required',
            'details.*.description' => 'required',
            'details.*.quantity' => 'required|integer',
            'details.*.unit' => 'required|string',
            'details.*.hazard_category' => 'required|string',
            'details.*.disposition' => 'required|string',
            'details.*.evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'details.*.remark' => 'nullable|string',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        // Simpan report header
        $report = ReportProductionNonconformity::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $validated['date'],
            'shift' => $shift,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan detail (loop)
        foreach ($validated['details'] as $index => $detail) {
            $evidencePath = null;

            if (isset($detail['evidence']) && $detail['evidence'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['evidence'];
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $evidencePath = $file->storeAs('evidence_product_nonconformities', $filename, 'public');
            }
            
            DetailProductionNonconformity::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'occurrence_time' => $detail['occurrence_time'],
                'description' => $detail['description'],
                'quantity' => $detail['quantity'],
                'unit' => $detail['unit'],
                'hazard_category' => $detail['hazard_category'],
                'disposition' => $detail['disposition'],
                'evidence' => $evidencePath,
                'remark' => $detail['remark'] ?? null,
            ]);
        }

        return redirect()->route('report_production_nonconformities.index')->with('success', 'Report berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportProductionNonconformity::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_production_nonconformities.index')
            ->with('success', 'Report berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportProductionNonconformity::findOrFail($id);
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
        $report = ReportProductionNonconformity::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function addDetail($uuid)
    {
        $report = ReportProductionNonconformity::where('uuid', $uuid)->firstOrFail();
        return view('report_production_nonconformities.add_detail', compact('report'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportProductionNonconformity::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'occurrence_time' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer',
            'hazard_category' => 'required|string',
            'unit' => 'required|string',
            'disposition' => 'required|string',
            'evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remark' => 'nullable|string',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $evidencePath = $file->storeAs('evidence_product_nonconformities', $filename, 'public');
        }

        DetailProductionNonconformity::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'occurrence_time' => $validated['occurrence_time'],
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'hazard_category' => $validated['hazard_category'],
            'disposition' => $validated['disposition'],
            'evidence' => $evidencePath,
            'remark' => $validated['remark'] ?? null,
        ]);

        return redirect()->route('report_production_nonconformities.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportProductionNonconformity::with('details', 'area')
            ->where('uuid', $uuid)
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

        $pdf = Pdf::loadView('report_production_nonconformities.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ]);

        return $pdf->stream('Report-Ketidaksesuaian-' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportProductionNonconformity::with('details')->where('uuid', $uuid)->firstOrFail();
        return view('report_production_nonconformities.edit', compact('report'));
    }

    public function update(Request $request, $uuid)
    {
        $report = ReportProductionNonconformity::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'details' => 'required|array',
            'details.*.occurrence_time' => 'required',
            'details.*.description' => 'required',
            'details.*.quantity' => 'required|integer',
            'details.*.unit' => 'required|string',
            'details.*.hazard_category' => 'required|string',
            'details.*.disposition' => 'required|string',
            'details.*.evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'details.*.remark' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update report header
            $report->update([
                'date' => $validated['date'],
                'shift' => $validated['shift'],
            ]);

            // Hapus detail lama (atau bisa update per detail UUID jika ada)
            $report->details()->delete();

            // Simpan detail baru
            foreach ($validated['details'] as $index => $detail) {
                $evidencePath = null;

                if (isset($detail['evidence']) && $detail['evidence'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $detail['evidence'];
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $evidencePath = $file->storeAs('evidence_product_nonconformities', $filename, 'public');
                }

                DetailProductionNonconformity::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'occurrence_time' => $detail['occurrence_time'],
                    'description' => $detail['description'],
                    'quantity' => $detail['quantity'],
                    'unit' => $detail['unit'],
                    'hazard_category' => $detail['hazard_category'],
                    'disposition' => $detail['disposition'],
                    'evidence' => $evidencePath,
                    'remark' => $detail['remark'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('report_production_nonconformities.index')->with('success', 'Report berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate: ' . $e->getMessage());
        }
    }



}