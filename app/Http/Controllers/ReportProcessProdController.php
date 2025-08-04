<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportProcessProd;
use App\Models\DetailProcessProd;
use App\Models\ItemDetailProd;
use App\Models\Product;
use App\Models\Formula;
use App\Models\Formulation;
use App\Models\Area;
use App\Models\Section;
use App\Models\ProcessSensoric;
use App\Models\ProcessEmulsifying;
use App\Models\ProcessTumbling;
use App\Models\ProcessAging;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;

class ReportProcessProdController extends Controller
{
    public function index()
    {
        $reports = ReportProcessProd::with([
            'area',
            'section',
            'detail.product',
            'detail.formula',
            'detail.items.formulation.rawMaterial',
            'detail.items.formulation.premix',
            'detail.emulsifying',
            'detail.sensoric',
            'detail.tumbling',
            'detail.aging'
        ])->latest()->get();

        return view('report_process_productions.index', compact('reports'));
    }


    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();
        $products = Product::all()->groupBy('product_name')
            ->map(function ($group) {
                return $group->first();
            });
        $formulas = Formula::all();
        $formulations = Formulation::all();

        return view('report_process_productions.create', compact('areas', 'sections', 'products', 'formulas', 'formulations'));
    }

    public function store(Request $request)
    {
        // Buat Report Utama
        $report = ReportProcessProd::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
        ]);

        // Simpan Detail Process
        $detail = DetailProcessProd::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'formula_uuid' => $request->formula_uuid,
            'production_code' => $request->production_code,
            'mixing_time' => $request->mixing_time,
            'rework_kg' => $request->rework_kg,
            'rework_percent' => $request->rework_percent,
            'total_material' => $request->total_material,
        ]);

        // Simpan Item Formulasi
        foreach ($request->formulation_uuids ?? [] as $uuid) {
            ItemDetailProd::create([
                'uuid' => Str::uuid(),
                'detail_uuid' => $detail->uuid,
                'formulation_uuid' => $uuid,
                'actual_weight' => $request->actual_weight[$uuid] ?? null,
                'sensory' => $request->sensory[$uuid] ?? null,
                'temperature' => $request->temperature[$uuid] ?? null,
            ]);
        }

        // Simpan Sensorik
        ProcessSensoric::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'homogeneous' => $request->homogeneous,
            'stiffness' => $request->stiffness,
            'aroma' => $request->aroma,
            'foreign_object' => $request->foreign_object,
        ]);

        // Simpan Emulsifying
        ProcessEmulsifying::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'standard_mixture_temp' => $request->standard_mixture_temp,
            'actual_mixture_temp_1' => $request->actual_mixture_temp_1,
            'actual_mixture_temp_2' => $request->actual_mixture_temp_2,
            'actual_mixture_temp_3' => $request->actual_mixture_temp_3,
            'average_mixture_temp' => $request->average_mixture_temp,
        ]);

        // Simpan Tumbling
        ProcessTumbling::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'tumbling_process' => $request->tumbling_process,
        ]);

        // Simpan Aging
        ProcessAging::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'aging_process' => $request->aging_process,
            'stuffing_result' => $request->stuffing_result,
        ]);

        return redirect()->route('report_process_productions.index')->with('success', 'Data berhasil disimpan.');
    }


    public function destroy($uuid)
    {
        $report = ReportProcessProd::where('uuid', $uuid)->firstOrFail();
        $report->delete();

        return redirect()->route('report_process_productions.index')->with('success', 'Data berhasil dihapus.');
    }

    public function getFormulas($productUuid)
    {
        $formulas = Formula::where('product_uuid', $productUuid)->get();
        return response()->json(['formulas' => $formulas]);
    }

    public function getFormulations($formulaUuid)
    {
        $formulations = Formulation::with(['rawMaterial', 'premix'])
            ->where('formula_uuid', $formulaUuid)
            ->get();

        $rawMaterials = $formulations->whereNotNull('raw_material_uuid')->values();
        $premixes = $formulations->whereNotNull('premix_uuid')->values();

        return response()->json([
            'raw_materials' => $rawMaterials,
            'premixes' => $premixes,
        ]);
    }

    public function addDetail($reportUuid)
    {
        $report = ReportProcessProd::where('uuid', $reportUuid)->firstOrFail();
        $products = Product::all();
        $formulas = Formula::all();
        $formulations = Formulation::all();

        return view('report_process_productions.add_detail', compact('report', 'products', 'formulas', 'formulations'));
    }

    public function storeDetail(Request $request, $reportUuid)
    {
        $report = ReportProcessProd::where('uuid', $reportUuid)->firstOrFail();

        $detail = DetailProcessProd::create([
            'uuid' => Str::uuid(),
            'report_uuid' => $report->uuid,
            'product_uuid' => $request->product_uuid,
            'formula_uuid' => $request->formula_uuid,
            'production_code' => $request->production_code,
            'mixing_time' => $request->mixing_time,
            'rework_kg' => $request->rework_kg,
            'rework_percent' => $request->rework_percent,
            'total_material' => $request->total_material,
        ]);

        foreach ($request->formulation_uuids ?? [] as $uuid) {
            ItemDetailProd::create([
                'uuid' => Str::uuid(),
                'detail_uuid' => $detail->uuid,
                'formulation_uuid' => $uuid,
                'actual_weight' => $request->actual_weight[$uuid] ?? null,
                'sensory' => $request->sensory[$uuid] ?? null,
                'temperature' => $request->temperature[$uuid] ?? null,
            ]);
        }

        ProcessSensoric::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'homogeneous' => $request->homogeneous,
            'stiffness' => $request->stiffness,
            'aroma' => $request->aroma,
            'foreign_object' => $request->foreign_object,
        ]);

        ProcessEmulsifying::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'standard_mixture_temp' => $request->standard_mixture_temp,
            'actual_mixture_temp_1' => $request->actual_mixture_temp_1,
            'actual_mixture_temp_2' => $request->actual_mixture_temp_2,
            'actual_mixture_temp_3' => $request->actual_mixture_temp_3,
            'average_mixture_temp' => $request->average_mixture_temp,
        ]);

        ProcessTumbling::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'tumbling_process' => $request->tumbling_process,
        ]);

        ProcessAging::create([
            'uuid' => Str::uuid(),
            'detail_uuid' => $detail->uuid,
            'aging_process' => $request->aging_process,
            'stuffing_result' => $request->stuffing_result,
        ]);

        return redirect()->route('report_process_productions.index')->with('success', 'Detail berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportProcessProd::findOrFail($id);
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
        $report = ReportProcessProd::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportProcessProd::with([
            'detail.product',
            'detail.formula',
            'detail.items.formulation.rawMaterial',
            'detail.items.formulation.premix',
            'detail.emulsifying',
            'detail.sensoric',
            'detail.tumbling',
            'detail.aging'
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

        $pdf = Pdf::loadView('report_process_productions.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Proses_Produksi_' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportProcessProd::with([
            'detail',
            'detail.product',
            'detail.formula',
            'detail.items.formulation.rawMaterial',
            'detail.items.formulation.premix',
            'detail.sensoric',
            'detail.emulsifying',
            'detail.tumbling',
            'detail.aging'
        ])->where('uuid', $uuid)->firstOrFail();

        $detail = $report->detail->first();

        $sections = Section::all();
        $products = Product::all()->groupBy('product_name')->map(fn($group) => $group->first());

        // Ambil formulas hanya untuk product yang dipilih
        $formulas = Formula::where('product_uuid', $detail->product_uuid)->get();

        return view('report_process_productions.edit', compact('report', 'detail', 'sections', 'products', 'formulas'));
    }



    public function update(Request $request, $uuid)
    {
        $report = ReportProcessProd::where('uuid', $uuid)->firstOrFail();

        // Update Report Utama
        $report->update([
            'section_uuid' => $request->section_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
        ]);

        $detail = $report->detail->first();
        if ($detail) {
            $detail->update([
                'product_uuid' => $request->product_uuid,
                'formula_uuid' => $request->formula_uuid,
                'production_code' => $request->production_code,
                'mixing_time' => $request->mixing_time,
                'rework_kg' => $request->rework_kg,
                'rework_percent' => $request->rework_percent,
                'total_material' => $request->total_material,
            ]);

            // Update Item Formulasi
            foreach ($request->formulation_uuids ?? [] as $uuidFm) {
                $item = $detail->items->where('formulation_uuid', $uuidFm)->first();
                if ($item) {
                    $item->update([
                        'actual_weight' => $request->actual_weight[$uuidFm] ?? null,
                        'sensory' => $request->sensory[$uuidFm] ?? null,
                        'temperature' => $request->temperature[$uuidFm] ?? null,
                    ]);
                } else {
                    ItemDetailProd::create([
                        'uuid' => Str::uuid(),
                        'detail_uuid' => $detail->uuid,
                        'formulation_uuid' => $uuidFm,
                        'actual_weight' => $request->actual_weight[$uuidFm] ?? null,
                        'sensory' => $request->sensory[$uuidFm] ?? null,
                        'temperature' => $request->temperature[$uuidFm] ?? null,
                    ]);
                }
            }

            // Update Sensorik
            if ($detail->sensoric) {
                $detail->sensoric->update([
                    'homogeneous' => $request->homogeneous,
                    'stiffness' => $request->stiffness,
                    'aroma' => $request->aroma,
                    'foreign_object' => $request->foreign_object,
                ]);
            }

            // Update Emulsifying
            if ($detail->emulsifying) {
                $detail->emulsifying->update([
                    'standard_mixture_temp' => $request->standard_mixture_temp,
                    'actual_mixture_temp_1' => $request->actual_mixture_temp_1,
                    'actual_mixture_temp_2' => $request->actual_mixture_temp_2,
                    'actual_mixture_temp_3' => $request->actual_mixture_temp_3,
                    'average_mixture_temp' => $request->average_mixture_temp,
                ]);
            }

            // Update Tumbling
            if ($detail->tumbling) {
                $detail->tumbling->update([
                    'tumbling_process' => $request->tumbling_process,
                ]);
            }

            // Update Aging
            if ($detail->aging) {
                $detail->aging->update([
                    'aging_process' => $request->aging_process,
                    'stuffing_result' => $request->stuffing_result,
                ]);
            }
        }

        return redirect()->route('report_process_productions.index')->with('success', 'Data berhasil diperbarui.');
    }

}