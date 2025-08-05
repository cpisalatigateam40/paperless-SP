<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\Formulation;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FormulaController extends Controller
{
    public function index()
    {
        $formulas = Formula::with(['product', 'area'])->get()->unique('product_name');

        return view('formulas.index', compact('formulas'));
    }

    public function create()
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();

        $areas = \App\Models\Area::all();
        return view('formulas.create', compact('products', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'formula_name' => 'required|string|max:255',
            'product_uuid' => 'nullable|exists:products,uuid',
        ]);

        $product = Product::where('uuid', $request->product_uuid)->first();

        Formula::create([
            'uuid' => Str::uuid(),
            'formula_name' => $request->formula_name,
            'product_uuid' => $request->product_uuid,
            'product_name' => $product?->product_name,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        return redirect()->route('formulas.index')->with('success', 'Formula created successfully.');
    }

    public function destroy($uuid)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();
        $formula->delete();

        return redirect()->route('formulas.index')->with('success', 'Formula deleted successfully.');
    }

    public function detail($uuid)
    {
        $formula = Formula::with(['product', 'area', 'formulations.rawMaterial'])->where('uuid', $uuid)->firstOrFail();
        $rawMaterials = \App\Models\RawMaterial::all();
        $premixes = \App\Models\Premix::all();
        return view('formulas.detail', compact('formula', 'rawMaterials', 'premixes'));
    }

    public function addDetail(Request $request, $uuid)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();

        $raws = $request->raw_material_uuid ?? [];
        $rawWeights = $request->raw_material_weight ?? [];

        $premixes = $request->premix_uuid ?? [];
        $premixWeights = $request->premix_weight ?? [];

        // validasi
        $request->validate([
            'formulation_name' => 'required|string|max:255',
            'raw_material_uuid' => 'nullable|array',
            'raw_material_uuid.*' => 'nullable|exists:raw_materials,uuid',
            'raw_material_weight' => 'nullable|array',
            'raw_material_weight.*' => 'nullable|numeric|min:0',

            'premix_uuid' => 'nullable|array',
            'premix_uuid.*' => 'nullable|exists:premixes,uuid',
            'premix_weight' => 'nullable|array',
            'premix_weight.*' => 'nullable|numeric|min:0',
        ]);

        // insert satu formulation per raw_material + berat
        foreach ($raws as $index => $raw_uuid) {
            Formulation::create([
                'uuid' => Str::uuid(),
                'formula_uuid' => $formula->uuid,
                'formulation_name' => $request->formulation_name,
                'raw_material_uuid' => $raw_uuid,
                'premix_uuid' => null,
                'weight' => isset($rawWeights[$index]) ? (float) $rawWeights[$index] : null,
            ]);
        }

        // insert satu formulation per premix + berat
        foreach ($premixes as $index => $premix_uuid) {
            Formulation::create([
                'uuid' => Str::uuid(),
                'formula_uuid' => $formula->uuid,
                'formulation_name' => $request->formulation_name,
                'raw_material_uuid' => null,
                'premix_uuid' => $premix_uuid,
                'weight' => isset($premixWeights[$index]) ? (float) $premixWeights[$index] : null,
            ]);
        }

        return redirect()->route('formulas.detail', $formula->uuid)
            ->with('success', 'Formulasi berhasil ditambahkan.');
    }

    public function deleteDetail($uuid, $detail_uuid)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();
        $detail = Formulation::where('uuid', $detail_uuid)->where('formula_uuid', $formula->uuid)->firstOrFail();
        $detail->delete();

        return redirect()->route('formulas.detail', $formula->uuid)->with('success', 'Detail deleted successfully.');
    }

    public function deleteDetailByName($uuid, $formulation_name)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();

        // Hapus semua detail dengan formulation_name sama
        Formulation::where('formula_uuid', $formula->uuid)
            ->where('formulation_name', $formulation_name)
            ->delete();

        return redirect()->route('formulas.detail', $formula->uuid)->with('success', 'Berhasil hapus data formulasi.');
    }

}