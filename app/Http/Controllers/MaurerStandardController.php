<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MaurerStandard;
use App\Models\Product;
use App\Models\MaurerProcessingStep;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;

class MaurerStandardController extends Controller
{
    public function index()
    {
        $standards = MaurerStandard::with(['product', 'processStep.area'])
            ->get()
            ->groupBy('product_uuid');

        $products = Product::all();

        return view('maurer-standards.index', compact('standards', 'products'));
    }


    public function create()
    {
        $products = Product::all();
        $area = Auth::user()->area_uuid;

        $defaultSteps = [
            'Drying I',
            'Drying II',
            'Drying III',
            'Drying IV',
            'Drying V',
            'Smoking',
            'Cooking I',
            'Cooking II'
        ];

        // Tambahkan step jika belum ada
        foreach ($defaultSteps as $index => $stepName) {
            MaurerProcessingStep::firstOrCreate(
                ['process_name' => $stepName],
                [
                    'uuid' => Str::uuid(),
                    'area_uuid' => Auth::user()->area_uuid,
                    'process_name' => $stepName,
                ]
            );
        }

        $steps = MaurerProcessingStep::with('area')
            ->whereIn('process_name', $defaultSteps)
            ->get();

        return view('maurer-standards.create', compact('products', 'steps'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_uuid' => 'required|uuid',
            'steps' => 'required|array',
            'steps.*.process_step_uuid' => 'required|uuid',
        ]);

        foreach ($request->steps as $stepData) {
            MaurerStandard::create([
                'uuid' => Str::uuid(),
                'product_uuid' => $request->product_uuid,
                'process_step_uuid' => $stepData['process_step_uuid'],
                'st_min' => $stepData['st_min'] ?? null,
                'st_max' => $stepData['st_max'] ?? null,
                'time_minute' => $stepData['time_minute'] ?? null,
                'rh_min' => $stepData['rh_min'] ?? null,
                'rh_max' => $stepData['rh_max'] ?? null,
                'ct_min' => $stepData['ct_min'] ?? null,
                'ct_max' => $stepData['ct_max'] ?? null,
            ]);
        }

        return redirect()->route('maurer-standards.index')->with('success', 'Standard Maurer berhasil disimpan.');
    }


    public function edit($uuid)
    {
        $standard = MaurerStandard::where('uuid', $uuid)->firstOrFail();
        $products = Product::orderBy('product_name')->get();
        $steps = MaurerProcessingStep::orderBy('process_name')->get();

        return view('maurer-standards.edit', compact('standard', 'products', 'steps'));
    }


    public function update(Request $request, $uuid)
    {
        $standard = MaurerStandard::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'product_uuid' => 'required|uuid',
            'process_step_uuid' => 'required|uuid',
        ]);

        $standard->update([
            'product_uuid' => $request->product_uuid,
            'process_step_uuid' => $request->process_step_uuid,
            'st_min' => $request->st_min,
            'st_max' => $request->st_max,
            'time_minute' => $request->time_minute,
            'rh_min' => $request->rh_min,
            'rh_max' => $request->rh_max,
            'ct_min' => $request->ct_min,
            'ct_max' => $request->ct_max,
        ]);

        return redirect()->route('maurer-standards.index')->with('success', 'Standard berhasil diubah.');
    }

    public function destroy($uuid)
    {
        $standard = MaurerStandard::where('uuid', $uuid)->firstOrFail();
        $standard->delete();

        return redirect()->route('maurer-standards.index')->with('success', 'Standard berhasil dihapus.');
    }

    public function addDetail($product_uuid)
    {
        $product = Product::where('uuid', $product_uuid)->firstOrFail();
        $steps = MaurerProcessingStep::orderBy('process_name')->get();

        return view('maurer-standards.add-detail', compact('product', 'steps'));
    }
}