<?php

namespace App\Http\Controllers;

use App\Models\ReportStuffer;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReportStufferController extends Controller
{
    public function index()
    {
        // ambil semua report (bisa tambah paginate)
        $reports = ReportStuffer::latest()->paginate(10);
        return view('report_stuffers.index', compact('reports'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();

        return view('report_stuffers.create', compact('products', 'areas'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            // Simpan report header
            $report = ReportStuffer::create([
                'uuid' => Str::uuid(),
                'date' => $request->date,
                'shift' => getShift(),
                'area_uuid' => Auth::user()->area_uuid,
                'created_by' => Auth::user()->name,
            ]);

            // Simpan detail_stuffers
            if ($request->has('detail_stuffers')) {
                foreach ($request->detail_stuffers as $detail) {
                    // Hitech
                    $report->detailStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $detail['product_uuid'],
                        'standard_weight' => $detail['standard_weight'],
                        'machine_name' => 'Hitech',
                        'range' => $detail['hitech_range'],
                        'avg' => $detail['hitech_avg'],
                        'note' => $detail['note'],
                    ]);
                    // Townsend
                    $report->detailStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $detail['product_uuid'],
                        'standard_weight' => $detail['standard_weight'],
                        'machine_name' => 'Townsend',
                        'range' => $detail['townsend_range'],
                        'avg' => $detail['townsend_avg'],
                        'note' => $detail['note'],
                    ]);
                }

            }

            // Simpan cooking_loss_stuffers
            if ($request->has('cooking_loss_stuffers')) {
                foreach ($request->cooking_loss_stuffers as $loss) {
                    $report->cookingLossStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $loss['product_uuid'] ?? null,
                        'machine_name' => 'Fessmann',
                        'percentage' => $loss['fessmann'] ?? null,
                    ]);
                    $report->cookingLossStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $loss['product_uuid'] ?? null,
                        'machine_name' => 'Maurer',
                        'percentage' => $loss['maurer'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('report_stuffers.index')
            ->with('success', 'Report dan detail berhasil disimpan!');
    }

    public function destroy($uuid)
    {
        $report = ReportStuffer::where('uuid', $uuid)->firstOrFail();

        $report->delete();

        return redirect()->route('report_stuffers.index')
            ->with('success', 'Report deleted successfully!');
    }

    public function addDetailForm($uuid)
    {
        $report = ReportStuffer::where('uuid', $uuid)->firstOrFail();
        $products = \App\Models\Product::all();

        return view('report_stuffers.add_detail', compact('report', 'products'));
    }

    public function storeDetail(Request $request, $uuid)
    {
        $report = ReportStuffer::where('uuid', $uuid)->firstOrFail();

        // Loop dan simpan detail_stuffers
        if ($request->has('detail_stuffers')) {
            foreach ($request->detail_stuffers as $detail) {
                // Hitech
                $report->detailStuffers()->create([
                    'uuid' => Str::uuid(),
                    'product_uuid' => $detail['product_uuid'],
                    'standard_weight' => $detail['standard_weight'],
                    'machine_name' => 'Hitech',
                    'range' => $detail['hitech_range'],
                    'avg' => $detail['hitech_avg'],
                    'note' => $detail['note'] ?? null,
                ]);
                // Townsend
                $report->detailStuffers()->create([
                    'uuid' => Str::uuid(),
                    'product_uuid' => $detail['product_uuid'],
                    'standard_weight' => $detail['standard_weight'],
                    'machine_name' => 'Townsend',
                    'range' => $detail['townsend_range'],
                    'avg' => $detail['townsend_avg'],
                    'note' => $detail['note'] ?? null,
                ]);
            }
        }

        // Loop dan simpan cooking_loss_stuffers
        if ($request->has('cooking_loss_stuffers')) {
            foreach ($request->cooking_loss_stuffers as $loss) {
                if ($loss['product_uuid']) { // biar product_uuid kosong tidak disimpan
                    $report->cookingLossStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $loss['product_uuid'] ?? null,
                        'machine_name' => 'Fessmann',
                        'percentage' => $loss['fessmann'] ?? null,
                    ]);
                    $report->cookingLossStuffers()->create([
                        'uuid' => Str::uuid(),
                        'product_uuid' => $loss['product_uuid'] ?? null,
                        'machine_name' => 'Maurer',
                        'percentage' => $loss['maurer'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('report_stuffers.index')->with('success', 'Detail berhasil ditambahkan!');
    }

    public function approve($id)
    {
        $report = ReportStuffer::findOrFail($id);
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
        $report = ReportStuffer::with(['detailStuffers.product', 'cookingLossStuffers.product'])
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

        $pdf = Pdf::loadView('report_stuffers.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Report_Stuffer_' . $report->date . '.pdf');
    }


}