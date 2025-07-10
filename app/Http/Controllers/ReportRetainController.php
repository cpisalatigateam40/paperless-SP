<?php

namespace App\Http\Controllers;

use App\Models\ReportRetain;
use App\Models\DetailRetain;
use App\Models\Area;
use App\Models\Section;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportRetainController extends Controller
{
    public function index()
    {
        $reports = ReportRetain::with('area', 'details.product')->latest()->get();
        return view('report_retains.index', compact('reports'));
    }

    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all();
        return view('report_retains.create', compact('areas', 'sections', 'products'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $report = ReportRetain::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid,
                'section_uuid' => $request->section_uuid,
                'date' => $request->date,
                'storage' => $request->has('storage') ? implode(', ', $request->storage) : null,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->details as $detail) {
                DetailRetain::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'product_uuid' => $detail['product_uuid'],
                    'production_code' => $detail['production_code'],
                    'best_before' => $detail['best_before'],
                    'quantity' => $detail['quantity'],
                    'notes' => $detail['notes'],
                ]);
            }
        });

        return redirect()->route('report_retains.index')->with('success', 'Data berhasil disimpan');
    }

    public function destroy($id)
    {
        $report = ReportRetain::findOrFail($id);
        $report->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function createDetail($report_uuid)
    {
        $report = ReportRetain::where('uuid', $report_uuid)->with('details')->firstOrFail();
        $products = Product::all();
        return view('report_retains.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $report_uuid)
    {
        $request->validate([
            'product_uuid' => 'required',
            'production_code' => 'required',
            'best_before' => 'required|date',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $report = ReportRetain::where('uuid', $report_uuid)->firstOrFail();

        DetailRetain::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'production_code' => $request->production_code,
            'best_before' => $request->best_before,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
        ]);

        return redirect()->route('report_retains.index')->with('success', 'Detail berhasil ditambahkan');
    }

    public function approve($id)
    {
        $report = ReportRetain::findOrFail($id);
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
        $report = ReportRetain::where('uuid', $uuid)
            ->with('details.product', 'area')
            ->firstOrFail();

        // Generate QR created_by
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);

        // Generate QR approved_by
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $pdf = Pdf::loadView('report_retains.export_pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('ReportRetain-' . $report->date . '.pdf');
    }
}