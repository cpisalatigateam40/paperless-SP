<?php

namespace App\Http\Controllers;

use App\Models\MasterBoilingTankStandard;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class MasterBoilingTankStandardController extends Controller
{
    public function index(Request $request)
    {
        $standards = MasterBoilingTankStandard::with(['product', 'area'])
            ->when($request->area_uuid, fn ($q) => $q->where('area_uuid', $request->area_uuid))
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('product', fn ($q2) => $q2->where('product_name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('master_boiling_tank_standards.index', [
            'standards' => $standards,
            'areas' => \App\Models\Area::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('master_boiling_tank_standards.form', [
            'standard' => null,
            'isEdit' => false,
            ...$this->sharedFormData(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_uuid' => [
                'required', 'uuid',
                Rule::unique('master_boiling_tank_standards', 'product_uuid')
                    ->where('area_uuid', Auth::user()->area_uuid),
            ],
            'suhu_tangki_1_min' => ['required', 'numeric'],
            'suhu_tangki_1_max' => ['nullable', 'numeric', 'gte:suhu_tangki_1_min'],
            'suhu_tangki_2_min' => ['required', 'numeric'],
            'suhu_tangki_2_max' => ['nullable', 'numeric', 'gte:suhu_tangki_2_min'],
            'berat_mentah_min' => ['required', 'numeric'],
            'berat_mentah_max' => ['nullable', 'numeric', 'gte:berat_mentah_min'],
            'berat_matang_min' => ['required', 'numeric'],
            'berat_matang_max' => ['nullable', 'numeric', 'gte:berat_matang_min'],
            'actual_core_temp_min' => ['required', 'numeric'],
            'actual_core_temp_max' => ['nullable', 'numeric', 'gte:actual_core_temp_min'],
        ], [
            'product_uuid.unique' => 'Produk ini sudah punya standar di area kamu, silakan edit yang sudah ada.',
        ]);

        MasterBoilingTankStandard::create([
            ...$validated,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        return redirect()->route('master_boiling_tank_standards.index')
            ->with('success', 'Master standar Boiling Tank berhasil disimpan');
    }

    public function edit(MasterBoilingTankStandard $master_boiling_tank_standard)
    {
        return view('master_boiling_tank_standards.form', [
            'standard' => $master_boiling_tank_standard,
            'isEdit' => true,
            ...$this->sharedFormData(),
        ]);
    }

    public function update(Request $request, MasterBoilingTankStandard $master_boiling_tank_standard)
    {
        $validated = $request->validate([
            'product_uuid' => [
                'required', 'uuid',
                Rule::unique('master_boiling_tank_standards', 'product_uuid')
                    ->where('area_uuid', Auth::user()->area_uuid)
                    ->ignore($master_boiling_tank_standard->uuid, 'uuid'),
            ],
            'suhu_tangki_1_min' => ['required', 'numeric'],
            'suhu_tangki_1_max' => ['nullable', 'numeric', 'gte:suhu_tangki_1_min'],
            'suhu_tangki_2_min' => ['required', 'numeric'],
            'suhu_tangki_2_max' => ['nullable', 'numeric', 'gte:suhu_tangki_2_min'],
            'berat_mentah_min' => ['required', 'numeric'],
            'berat_mentah_max' => ['nullable', 'numeric', 'gte:berat_mentah_min'],
            'berat_matang_min' => ['required', 'numeric'],
            'berat_matang_max' => ['nullable', 'numeric', 'gte:berat_matang_min'],
            'actual_core_temp_min' => ['required', 'numeric'],
            'actual_core_temp_max' => ['nullable', 'numeric', 'gte:actual_core_temp_min'],
        ], [
            'product_uuid.unique' => 'Produk ini sudah punya standar di area kamu, silakan edit yang sudah ada.',
        ]);

        $master_boiling_tank_standard->update($validated);

        return redirect()->route('master_boiling_tank_standards.index')
            ->with('success', 'Master standar Boiling Tank berhasil diperbarui');
    }

    private function sharedFormData(): array
    {
        return [
            'products' => Product::selectRaw('MIN(uuid) as uuid, product_name')
                ->groupBy('product_name')
                ->get(),
        ];
    }

    public function getByProduct(string $product_uuid)
    {
        $standard = MasterBoilingTankStandard::where('area_uuid', Auth::user()->area_uuid)
            ->where('product_uuid', $product_uuid)
            ->first();

        if (!$standard) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'suhu_tangki_1_label' => $standard->suhu_tangki_1_label,
            'suhu_tangki_1_min' => $standard->suhu_tangki_1_min,
            'suhu_tangki_2_label' => $standard->suhu_tangki_2_label,
            'suhu_tangki_2_min' => $standard->suhu_tangki_2_min,
            'berat_mentah_label' => $standard->berat_mentah_label,
            'berat_mentah_min' => $standard->berat_mentah_min,
            'actual_core_temp_label' => $standard->actual_core_temp_label,
            'actual_core_temp_min' => $standard->actual_core_temp_min,
            'berat_matang_label' => $standard->berat_matang_label,
            'berat_matang_min' => $standard->berat_matang_min,
        ]);
    }
}