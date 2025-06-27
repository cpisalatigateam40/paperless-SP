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

    public function store(Request $request)
    {
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
        ]);

        $report = ReportForeignObject::create([
            'uuid' => Str::uuid(),
            'date' => $request->date,
            'shift' => getShift(),
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
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $evidencePath = $file->storeAs('evidence_foreign_objects', $filename, 'public');
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

        $pdf = Pdf::loadView('report_foreign_objects.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Kontaminasi_' . $report->date->format('Ymd') . '.pdf');
    }


}