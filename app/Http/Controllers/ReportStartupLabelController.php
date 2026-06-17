<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\ReportStartupLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportStartupLabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reports = ReportStartupLabel::with('area')
            ->latest('date')
            ->paginate(10);

        return view('report_startup_labels.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();
        $products = Product::all();

        return view('report_startup_labels.form', compact('areas', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_uuid'   => 'nullable|uuid|exists:areas,uuid',
            'date'        => 'nullable|date',
            'shift'       => 'nullable|string|max:255',
            'created_by'  => 'nullable|string|max:255',
            'known_by'    => 'nullable|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'approved_at' => 'nullable|date',

            'details'                     => 'nullable|array',
            'details.*.product_uuid'      => 'nullable|uuid|exists:products,uuid',
            'details.*.time'              => 'nullable',
            'details.*.production_code'   => 'nullable|string|max:255',
            'details.*.best_before'       => 'nullable|date',
            'details.*.result'            => 'nullable|string|max:255',
            'details.*.corrective_action' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $report = ReportStartupLabel::create([
                'area_uuid'   => $validated['area_uuid'] ?? null,
                'date'        => $validated['date'] ?? null,
                'shift'       => $validated['shift'] ?? null,
                'created_by'  => $validated['created_by'] ?? null,
                'known_by'    => $validated['known_by'] ?? null,
                'approved_by' => $validated['approved_by'] ?? null,
                'approved_at' => $validated['approved_at'] ?? null,
            ]);

            foreach ($validated['details'] ?? [] as $detail) {
                $report->details()->create($detail);
            }
        });

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Report startup label berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $report = ReportStartupLabel::with(['area', 'details.product'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('report_startup_labels.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $report = ReportStartupLabel::with('details')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $areas = Area::all();
        $products = Product::all();

        return view('report_startup_labels.form', compact('report', 'areas', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $report = ReportStartupLabel::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'area_uuid'   => 'nullable|uuid|exists:areas,uuid',
            'date'        => 'nullable|date',
            'shift'       => 'nullable|string|max:255',
            'created_by'  => 'nullable|string|max:255',
            'known_by'    => 'nullable|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'approved_at' => 'nullable|date',

            'details'                     => 'nullable|array',
            'details.*.product_uuid'      => 'nullable|uuid|exists:products,uuid',
            'details.*.time'              => 'nullable',
            'details.*.production_code'   => 'nullable|string|max:255',
            'details.*.best_before'       => 'nullable|date',
            'details.*.result'            => 'nullable|string|max:255',
            'details.*.corrective_action' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $report) {
            $report->update([
                'area_uuid'   => $validated['area_uuid'] ?? null,
                'date'        => $validated['date'] ?? null,
                'shift'       => $validated['shift'] ?? null,
                'created_by'  => $validated['created_by'] ?? null,
                'known_by'    => $validated['known_by'] ?? null,
                'approved_by' => $validated['approved_by'] ?? null,
                'approved_at' => $validated['approved_at'] ?? null,
            ]);

            // Cara simpel: hapus semua detail lama, lalu simpan ulang sesuai input
            $report->details()->delete();

            foreach ($validated['details'] ?? [] as $detail) {
                $report->details()->create($detail);
            }
        });

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Report startup label berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $report = ReportStartupLabel::where('uuid', $uuid)->firstOrFail();
        $report->delete(); // detail ikut terhapus karena FK cascade

        return redirect()
            ->route('report_startup_labels.index')
            ->with('success', 'Report startup label berhasil dihapus.');
    }
}