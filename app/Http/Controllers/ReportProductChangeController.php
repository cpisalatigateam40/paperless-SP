<?php

namespace App\Http\Controllers;

use App\Models\ReportProductChange;
use App\Models\Product;
use App\Models\Area;
use App\Models\Equipment;
use App\Models\Section;
use App\Models\VerificationEquipment;
use App\Models\VerificationSection;
use App\Models\VerificationMaterialLeftover;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportProductChangeController extends Controller
{
    public function index()
    {
        $reports = ReportProductChange::with('product')->latest()->get();
        return view('report_product_changes.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();
        $equipments = Equipment::all();
        $sections = Section::all();

        $materialItems = [
            'Sisa Bahan',
            'Sisa Kemasan Plastik',
            'Sisa Kemasan Karton',
            'Sisa Labelisasi Plastik',
            'Sisa Labelisasi Karton',
        ];

        return view('report_product_changes.create', compact('products', 'areas', 'equipments', 'sections', 'materialItems'));
    }

    public function store(Request $request)
    {
        $uuid = Str::uuid()->toString();

        $report = ReportProductChange::create([
            'uuid' => $uuid,
            'area_uuid' => Auth::user()->area_uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'date' => $request->date,
            'shift' => getShift(),
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        // Sisa Bahan & Kemasan
        foreach ($request->input('material_leftovers', []) as $item) {
            VerificationMaterialLeftover::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'item' => $item['item'] ?? null,
                'condition' => $item['condition'] ?? null,
                'corrective_action' => $item['corrective_action'] ?? null,
                'verification' => $item['verification'] ?? null,
            ]);
        }

        // Mesin & Peralatan
        foreach ($request->input('equipments', []) as $item) {
            VerificationEquipment::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'equipment_uuid' => $item['equipment_uuid'] ?? null,
                'condition' => $item['condition'] ?? null,
                'corrective_action' => $item['corrective_action'] ?? null,
                'verification' => $item['verification'] ?? null,
            ]);
        }

        // Kondisi Ruangan
        foreach ($request->input('sections', []) as $item) {
            VerificationSection::create([
                'uuid' => Str::uuid(),
                'report_uuid' => $uuid,
                'section_uuid' => $item['section_uuid'] ?? null,
                'condition' => $item['condition'] ?? null,
                'corrective_action' => $item['corrective_action'] ?? null,
                'verification' => $item['verification'] ?? null,
            ]);
        }

        return redirect()->route('report_product_changes.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportProductChange::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportProductChange::findOrFail($id);
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
        $report = ReportProductChange::with([
            'product',
            'area',
            'materialLeftovers',
            'equipments.equipment',
            'sections.section'
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

        $pdf = Pdf::loadView('report_product_changes.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ]);
        return $pdf->stream('Laporan Verifikasi Produk - ' . $report->date . '.pdf');
    }
}