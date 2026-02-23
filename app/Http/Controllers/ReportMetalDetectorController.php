<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportMetalDetector;
use App\Models\DetailMetalDetector;
use App\Models\Area;
use App\Models\Section;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\MetalDetectorTemplateExport;
use App\Imports\MetalDetectorImport;
use Maatwebsite\Excel\Facades\Excel;

class ReportMetalDetectorController extends Controller
{

    public function index(Request $request)
    {
        $query = ReportMetalDetector::with(['area', 'section', 'details.product'])
            ->latest();

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

                // 🔹 SECTION
                $q->orWhereHas('section', function ($s) use ($search) {
                    $s->where('section_name', 'like', "%{$search}%");
                });

                // 🔹 DETAIL METAL DETECTOR
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('hour', 'like', "%{$search}%")
                    ->orWhere('production_code', 'like', "%{$search}%")
                    ->orWhere('result_fe', 'like', "%{$search}%")
                    ->orWhere('result_non_fe', 'like', "%{$search}%")
                    ->orWhere('result_sus316', 'like', "%{$search}%")
                    ->orWhere('verif_loma', 'like', "%{$search}%")
                    ->orWhere('nonconformity', 'like', "%{$search}%")
                    ->orWhere('corrective_action', 'like', "%{$search}%")
                    ->orWhere('verif_after_correct', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");

                    // 🔹 PRODUCT
                    $d->orWhereHas('product', function ($p) use ($search) {
                        // SESUAIKAN kolom produk kamu
                        $p->where('product_name', 'like', "%{$search}%")
                        ->orWhere('production_code', 'like', "%{$search}%");
                    });
                });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        // 🔥 HITUNG KETIDAKSESUAIAN
        $reports->getCollection()->transform(function ($report) {
            $report->ketidaksesuaian = $report->details->filter(function ($d) {
                return in_array('x', [
                    $d->result_fe,
                    $d->result_non_fe,
                    $d->result_sus316,
                    $d->verif_loma,
                ]);
            })->count();

            return $report;
        });

        return view('report_metal_detectors.index', compact('reports'));
    }


    // Form create
    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();

        return view('report_metal_detectors.create', compact('areas', 'sections', 'products'));
    }

    // Simpan report & detail
    public function store(Request $request)
    {
        // Validasi (minimal)
        $request->validate([
            'date' => 'required|date',
            'section_uuid' => 'nullable',
            'details' => 'required|array',
            'details.*.product_uuid' => 'required',
            'details.*.hour' => 'required',
            'details.*.production_code' => 'required',
            'details.*.notes' => 'nullable',
        ]);

        $shift = auth()->user()->hasRole('QC Inspector')
        ? session('shift_number') . '-' . session('shift_group')
        : ($request->shift ?? 'NON-SHIFT');

        // Buat report
        $report = ReportMetalDetector::create([
            'uuid' => Str::uuid(),
            'date' => $request->date,
            'shift' => $shift,
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'created_by' => Auth::user()->name,
        ]);

        // Buat detail
        foreach ($request->details as $detail) {
            DetailMetalDetector::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'hour' => $detail['hour'],
                'production_code' => $detail['production_code'],
                'result_fe' => $detail['result_fe'],
                'result_non_fe' => $detail['result_non_fe'],
                'result_sus316' => $detail['result_sus316'],
                'verif_loma' => $detail['verif_loma'],
                'nonconformity' => $detail['nonconformity'],
                'corrective_action' => $detail['corrective_action'],
                'verif_after_correct' => $detail['verif_after_correct'],
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        return redirect()->route('report_metal_detectors.index')
            ->with('success', 'Report berhasil disimpan!');
    }

    public function destroy($id)
    {
        $report = ReportMetalDetector::findOrFail($id);
        $report->delete();

        return redirect()->route('report_metal_detectors.index')
            ->with('success', 'Report berhasil dihapus!');
    }

    public function addDetail($report_uuid)
    {
        $report = ReportMetalDetector::where('uuid', $report_uuid)->with(['area', 'section'])->firstOrFail();
        $products = Product::all();

        return view('report_metal_detectors.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'hour' => 'required',
            'production_code' => 'required',
            'result_fe' => 'required|in:√,x',
            'result_non_fe' => 'required|in:√,x',
            'result_sus316' => 'required|in:√,x',
            'notes' => 'nullable',
            'verif_loma' => 'nullable',
            'nonconformity' => 'nullable',
            'corrective_action' => 'nullable',
            'verif_after_correct' => 'nullable',
        ]);

        $report = ReportMetalDetector::where('uuid', $report_uuid)->firstOrFail();

        DetailMetalDetector::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'hour' => $request->hour,
            'production_code' => $request->production_code,
            'result_fe' => $request->result_fe,
            'result_non_fe' => $request->result_non_fe,
            'result_sus316' => $request->result_sus316,
            'verif_loma' => $request->verif_loma,
            'nonconformity' => $request->nonconformity,
            'corrective_action' => $request->corrective_action,
            'verif_after_correct' => $request->verif_after_correct,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_metal_detectors.index')->with('success', 'Detail berhasil ditambahkan!');
    }

    public function known($id)
    {
        $report = ReportMetalDetector::findOrFail($id);
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
        $report = ReportMetalDetector::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function exportPdf($report_uuid)
    {
        $report = ReportMetalDetector::where('uuid', $report_uuid)
            ->with(['details.product', 'area', 'section'])
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

        $pdf = Pdf::loadView('report_metal_detectors.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('report-metal-detector-' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportMetalDetector::where('uuid', $uuid)->firstOrFail();
        $details = DetailMetalDetector::where('report_uuid', $report->uuid)->get();
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();

        return view('report_metal_detectors.edit', compact(
            'report',
            'details',
            'areas',
            'sections',
            'products'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required',
            'section_uuid' => 'nullable',
            'details' => 'required|array',
            'details.*.product_uuid' => 'required',
            'details.*.hour' => 'required',
            'details.*.production_code' => 'required',
        ]);

        // Update header laporan
        $report = ReportMetalDetector::where('uuid', $uuid)->firstOrFail();
        $report->update([
            'date' => $request->date,
            'shift' => $request->shift,
            'section_uuid' => $request->section_uuid,
        ]);

        // Hapus semua detail lama dan simpan ulang (praktis untuk form dinamis)
        DetailMetalDetector::where('report_uuid', $report->uuid)->delete();

        foreach ($request->details as $detail) {
            DetailMetalDetector::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $report->uuid,
                'product_uuid' => $detail['product_uuid'],
                'hour' => $detail['hour'],
                'production_code' => $detail['production_code'],
                'result_fe' => $detail['result_fe'],
                'result_non_fe' => $detail['result_non_fe'],
                'result_sus316' => $detail['result_sus316'],
                'verif_loma' => $detail['verif_loma'],
                'nonconformity' => $detail['nonconformity'],
                'corrective_action' => $detail['corrective_action'],
                'verif_after_correct' => $detail['verif_after_correct'],
                'notes' => $detail['notes'] ?? null,
            ]);
        }

        return redirect()->route('report_metal_detectors.index')->with('success', 'Data berhasil diperbarui!');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new MetalDetectorTemplateExport,
            'template_pemeriksaan_md_adonan.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new MetalDetectorImport, $request->file('file'));

        return redirect()
            ->route('report_metal_detectors.index')
            ->with('success', 'Data Excel berhasil diimport.');
    }


}