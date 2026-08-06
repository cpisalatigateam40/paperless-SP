<?php

namespace App\Http\Controllers;

use App\Models\DetailAuditPackingPrimer;
use App\Models\MasterAuditPackingPrimerItem;
use App\Models\Product;
use App\Models\ReportAuditPackingPrimer;
use App\Models\Section;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Traits\HasBulkPdfExport;

class ReportAuditPackingPrimerController extends Controller
{
    use HasBulkPdfExport;

    protected string $bulkModel = ReportAuditPackingPrimer::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportAuditPackingPrimer::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_audit_packing_primers.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area', 'section', 'product', 'details.item', 'createdBy',];
    }

    protected function getBulkExportExtraData($report): array
    {
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQr = QrCode::format('png')->size(150)->generate($createdInfo);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQr = QrCode::format('png')->size(150)->generate($approvedInfo);

        $knownInfo = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
        $knownQr = QrCode::format('png')->size(150)->generate($knownInfo);

        return [
            'createdQr'  => 'data:image/png;base64,' . base64_encode($createdQr),
            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQr),
            'knownQr'    => 'data:image/png;base64,' . base64_encode($knownQr),
        ];
    }
    
    protected function getBulkExportFileName(): string
    {
        return 'laporan_audit_packing';
    }

    public function index(Request $request)
    {
        $reports = ReportAuditPackingPrimer::with(['section', 'product', 'details.item'])
            ->when($request->filled('date'), fn ($q) => $q->whereDate('date', $request->date))
            ->when($request->filled('section_uuid'), fn ($q) => $q->where('section_uuid', $request->section_uuid))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('product', function ($q2) use ($request) {
                    $q2->where('product_name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('area'), fn ($q) => $q->where('area_uuid', $request->area))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        $areas = Area::all();

        return view('report_audit_packing_primers.index', compact('reports', 'areas'));
    }

    public function create()
    {
        $sections = Section::orderBy('section_name')->get();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $items = MasterAuditPackingPrimerItem::active()->ordered()->get()->groupBy('category');
        $report = null;
        $isEdit = false;

        return view('report_audit_packing_primers.form', compact('sections', 'products', 'items', 'report', 'isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_uuid' => ['required', 'uuid', 'exists:sections,uuid'],
            'product_uuid' => ['nullable', 'uuid', 'exists:products,uuid'],
            'date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:255'],
            'line' => ['nullable', 'string', 'max:255'],
            'production_code' => ['nullable', 'string', 'max:255'],
            'karyawan' => ['nullable', 'string', 'max:255'],
            'audit_score' => ['nullable', 'string', 'max:255'],
            'tindakan' => ['nullable', 'string'],
            'verifikasi' => ['required', 'array'],
            'verifikasi.*' => ['required', 'in:yes,no'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string'],
            'tujuan' => ['nullable', 'string'],
        ]);

        $report = DB::transaction(function () use ($validated, $request) {
            $report = ReportAuditPackingPrimer::create([
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $validated['section_uuid'],
                'product_uuid' => $validated['product_uuid'] ?? null,
                'date' => $validated['date'],
                'shift' => $validated['shift'] ?? null,
                'line' => $validated['line'] ?? null,
                'production_code' => $validated['production_code'] ?? null,
                'karyawan' => $validated['karyawan'] ?? null,
                'audit_score' => $validated['audit_score'] ?? null,
                'tindakan' => $validated['tindakan'] ?? null,
                'created_by' => Auth::user()->uuid,
                'tujuan' => $validated['tujuan'] ?? null,
            ]);

            $items = MasterAuditPackingPrimerItem::active()->ordered()->get();

            foreach ($items as $item) {
                DetailAuditPackingPrimer::create([
                    'report_uuid' => $report->uuid,
                    'item_uuid' => $item->uuid,
                    'verifikasi' => $request->input("verifikasi.{$item->uuid}"),
                    'keterangan' => $request->input("keterangan.{$item->uuid}"),
                ]);
            }

            return $report;
        });

        return redirect()
            ->route('report_audit_packing_primers.index')
            ->with('success', 'Checklist audit packing primer berhasil disimpan.');
    }

    public function edit(string $uuid)
    {
        $report = ReportAuditPackingPrimer::with(['details.item'])->findOrFail($uuid);
        $sections = Section::orderBy('section_name')->get();
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $items = MasterAuditPackingPrimerItem::active()->ordered()->get()->groupBy('category');
        $isEdit = true;

        return view('report_audit_packing_primers.form', compact('report', 'sections', 'products', 'items', 'isEdit'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportAuditPackingPrimer::findOrFail($uuid);

        $validated = $request->validate([
            'section_uuid' => ['required', 'uuid', 'exists:sections,uuid'],
            'product_uuid' => ['nullable', 'uuid', 'exists:products,uuid'],
            'date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:255'],
            'line' => ['nullable', 'string', 'max:255'],
            'production_code' => ['nullable', 'string', 'max:255'],
            'karyawan' => ['nullable', 'string', 'max:255'],
            'audit_score' => ['nullable', 'string', 'max:255'],
            'tindakan' => ['nullable', 'string'],
            'verifikasi' => ['required', 'array'],
            'verifikasi.*' => ['required', 'in:yes,no'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string'],
            'tujuan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($report, $validated, $request) {
            // approved_at / approved_by tidak pernah ditimpa lewat update()
            $report->update([
                'section_uuid' => $validated['section_uuid'],
                'product_uuid' => $validated['product_uuid'] ?? null,
                'date' => $validated['date'],
                'shift' => $validated['shift'] ?? null,
                'line' => $validated['line'] ?? null,
                'production_code' => $validated['production_code'] ?? null,
                'karyawan' => $validated['karyawan'] ?? null,
                'audit_score' => $validated['audit_score'] ?? null,
                'tindakan' => $validated['tindakan'] ?? null,
                'tujuan' => $validated['tujuan'] ?? null,
            ]);

            $items = MasterAuditPackingPrimerItem::active()->ordered()->get();

            foreach ($items as $item) {
                DetailAuditPackingPrimer::updateOrCreate(
                    [
                        'report_uuid' => $report->uuid,
                        'item_uuid' => $item->uuid,
                    ],
                    [
                        'verifikasi' => $request->input("verifikasi.{$item->uuid}"),
                        'keterangan' => $request->input("keterangan.{$item->uuid}"),
                    ]
                );
            }
        });

        return redirect()
            ->route('report_audit_packing_primers.index')
            ->with('success', 'Checklist audit packing primer berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $report = ReportAuditPackingPrimer::findOrFail($uuid);
        $report->delete();

        return redirect()
            ->route('report_audit_packing_primers.index')
            ->with('success', 'Checklist audit packing primer berhasil dihapus.');
    }

    public function exportPdf(string $uuid)
    {
        $report = ReportAuditPackingPrimer::with([
            'area', 'section', 'product', 'details.item', 'createdBy',
        ])->where('uuid', $uuid)->firstOrFail();
 
        // Generate QR untuk Pelaksana (created_by)
        $createdByName = $report->createdBy->name ?? $report->created_by;
        $createdInfo = "Dibuat oleh: {$createdByName}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);
 
        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_audit_packing_primers');
 
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report_audit_packing_primers.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'formNumber' => $formNumber,
        ])->setPaper('A4', 'portrait');
 
        return $pdf->stream('checklist_audit_packing_primer_' . $report->date . '.pdf');
    }
}