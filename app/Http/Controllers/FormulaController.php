<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\Formulation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FormulaController extends Controller
{
    public function index()
    {
        $formulas = Formula::with(['product', 'area'])->get();
        return view('formulas.index', compact('formulas'));
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        $areas = \App\Models\Area::all();
        return view('formulas.create', compact('products', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'formula_name' => 'required|string|max:255',
            'product_uuid' => 'nullable|exists:products,uuid',
        ]);

        Formula::create([
            'uuid' => Str::uuid(),
            'formula_name' => $request->formula_name,
            'product_uuid' => $request->product_uuid,
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
        return view('formulas.detail', compact('formula', 'rawMaterials'));
    }

    public function addDetail(Request $request, $uuid)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'formulation_name' => 'required|string|max:255',
            'raw_material_uuid' => 'nullable|exists:raw_materials,uuid',
        ]);

        Formulation::create([
            'uuid' => Str::uuid(),
            'formula_uuid' => $formula->uuid,
            'formulation_name' => $request->formulation_name,
            'raw_material_uuid' => $request->raw_material_uuid,
        ]);

        return redirect()->route('formulas.detail', $formula->uuid)->with('success', 'Detail added successfully.');
    }

    public function deleteDetail($uuid, $detail_uuid)
    {
        $formula = Formula::where('uuid', $uuid)->firstOrFail();
        $detail = Formulation::where('uuid', $detail_uuid)->where('formula_uuid', $formula->uuid)->firstOrFail();
        $detail->delete();

        return redirect()->route('formulas.detail', $formula->uuid)->with('success', 'Detail deleted successfully.');
    }
}