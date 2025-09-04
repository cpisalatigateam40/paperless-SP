<?php

namespace App\Http\Controllers;

use App\Models\ReportForeignObject;
use App\Models\DetailForeignObject;
use App\Models\Product;
use App\Models\Section;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportForeignObjectController extends Controller
{
    public function index()
    {
        $reports = ReportForeignObject::with('section', 'area', 'details.product')
            ->latest()
            ->get();

        return view('report_foreign_objects.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();

        return view('report_foreign_objects.create', compact('areas', 'sections', 'products'));
    }

    private function saveSignature($base64Image, $prefix)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, dll

            $image = base64_decode($image);
            if ($image === false) {
                return null;
            }

            $fileName = $prefix . '_' . time() . '.' . $type;
            $filePath = 'signatures/' . $fileName;

            if (!Storage::disk('public')->exists('signatures')) {
                Storage::disk('public')->makeDirectory('signatures');
            }

            Storage::disk('public')->put($filePath, $image);

            return $filePath;
        }

        return null;
    }



    public function store(Request $request)
    {
        // dd($request->details);
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'section_uuid' => 'required|uuid',
            'details' => 'required|array|min:1',
            'details.*.time' => 'required',
            'details.*.product_uuid' => 'required|uuid',
            'details.*.production_code' => 'nullable|string',
            'details.*.contaminant_type' => 'nullable|string',
            'details.*.evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'details.*.analysis_stage' => 'nullable|string',
            'details.*.contaminant_origin' => 'nullable|string',
            'details.*.notes' => 'nullable|string',
            'details.*.qc_paraf' => 'nullable|string',
            'details.*.production_paraf' => 'nullable|string',
            'details.*.engineering_paraf' => 'nullable|string',
        ]);

        $report = ReportForeignObject::create([
            'uuid' => Str::uuid(),
            'date' => $request->date,
            'shift' => $request->shift,
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'created_by' => Auth::user()->name,
        ]);

        foreach ($request->details as $index => $detail) {
            $evidencePath = null;

            if (isset($detail['evidence']) && $detail['evidence'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $detail['evidence'];
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $evidencePath = $file->storeAs('evidence_foreign_objects', $filename, 'public');
            }

            // === Simpan paraf tanda tangan digital ===
            $qcParafPath = null;
            if (!empty($detail['qc_paraf'])) {
                $qcParafPath = $this->saveSignature($detail['qc_paraf'], "qc_{$index}");
            }

            $productionParafPath = null;
            if (!empty($detail['production_paraf'])) {
                $productionParafPath = $this->saveSignature($detail['production_paraf'], "production_{$index}");
            }

            $engineeringParafPath = null;
            if (!empty($detail['engineering_paraf'])) {
                $engineeringParafPath = $this->saveSignature($detail['engineering_paraf'], "engineering_{$index}");
            }

            DetailForeignObject::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'time' => $detail['time'],
                'production_code' => $detail['production_code'] ?? null,
                'contaminant_type' => $detail['contaminant_type'] ?? null,
                'evidence' => $evidencePath,
                'analysis_stage' => $detail['analysis_stage'] ?? null,
                'contaminant_origin' => $detail['contaminant_origin'] ?? null,
                'notes' => $detail['notes'] ?? null,
                'qc_paraf' => $qcParafPath,
                'production_paraf' => $productionParafPath,
                'engineering_paraf' => $engineeringParafPath,
            ]);
        }

        return redirect()->route('report-foreign-objects.index')->with('success', 'Laporan berhasil disimpan');
    }

    public function createDetail($uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();

        return view('report_foreign_objects.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'time' => 'required',
            'product_uuid' => 'required|uuid',
            'production_code' => 'nullable|string',
            'contaminant_type' => 'nullable|string',
            'evidence' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'analysis_stage' => 'nullable|string',
            'contaminant_origin' => 'nullable|string',
            'notes' => 'nullable|string',
            'qc_paraf' => 'nullable|string',
            'production_paraf' => 'nullable|string',
            'engineering_paraf' => 'nullable|string',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $evidencePath = $file->storeAs('evidence_foreign_objects', $filename, 'public');
        }

        // === Simpan paraf tanda tangan digital ===
        $qcParafPath = null;
        if (!empty($request->qc_paraf)) {
            $qcParafPath = $this->saveSignature($request->qc_paraf, "qc");
        }

        $productionParafPath = null;
        if (!empty($request->production_paraf)) {
            $productionParafPath = $this->saveSignature($request->production_paraf, "production");
        }

        $engineeringParafPath = null;
        if (!empty($request->engineering_paraf)) {
            $engineeringParafPath = $this->saveSignature($request->engineering_paraf, "engineering");
        }

        DetailForeignObject::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'time' => $request->time,
            'production_code' => $request->production_code,
            'contaminant_type' => $request->contaminant_type,
            'evidence' => $evidencePath,
            'analysis_stage' => $request->analysis_stage,
            'contaminant_origin' => $request->contaminant_origin,
            'notes' => $request->notes,
            'qc_paraf' => $qcParafPath,
            'production_paraf' => $productionParafPath,
            'engineering_paraf' => $engineeringParafPath,
        ]);

        return redirect()->route('report-foreign-objects.index')->with('success', 'Detail berhasil ditambahkan');
    }


    public function destroy($uuid)
    {
        $report = ReportForeignObject::where('uuid', $uuid)->firstOrFail();

        // Hapus semua file evidence terkait
        foreach ($report->details as $detail) {
            if ($detail->evidence && Storage::disk('public')->exists($detail->evidence)) {
                Storage::disk('public')->delete($detail->evidence);
            }
        }

        // Hapus semua detail
        $report->details()->delete();

        // Hapus report utama
        $report->delete();

        return redirect()->route('report-foreign-objects.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function known($id)
    {
        $report = ReportForeignObject::findOrFail($id);
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
        $report = ReportForeignObject::findOrFail($id);
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
        $report = ReportForeignObject::with('area', 'section', 'details.product')
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

        $pdf = Pdf::loadView('report_foreign_objects.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Kontaminasi_' . $report->date->format('Ymd') . '.pdf');
    }


}