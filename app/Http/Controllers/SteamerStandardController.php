<?php

namespace App\Http\Controllers;

use App\Models\SteamerStandard;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SteamerStandardController extends Controller
{
    public function index(Request $request)
    {
        $query = SteamerStandard::with(['product', 'area']);

        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        if ($request->filled('product_uuid')) {
            $query->where('product_uuid', $request->product_uuid);
        }

        $steamerStandards = $query->latest()->paginate(20);

        if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {

            $areas = Area::orderBy('name')->get();

        } else {

            $areas = collect();
        }

        return view('steamer_standards.index', compact('steamerStandards', 'areas'));
    }

    public function create()
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('steamer_standards.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_uuid' => [
                'required',
                'uuid',
                Rule::unique('steamer_standards')->where(fn ($q) => $q->where('area_uuid', Auth::user()->area_uuid)),
            ],
            'room_temp_min' => 'nullable|numeric',
            'room_temp_max' => 'nullable|numeric|gte:room_temp_min',
            'setup_time_min' => 'nullable|integer|min:0',
            'setup_time_max' => 'nullable|integer|gte:setup_time_min',
            'core_temp_min' => 'nullable|numeric',
            'core_temp_max' => 'nullable|numeric|gte:core_temp_min',
        ]);

        SteamerStandard::create([
            'area_uuid' => Auth::user()->area_uuid,
            ...$validated,
        ]);

        return redirect()->route('steamer-standards.index')
            ->with('success', 'Standar steamer berhasil ditambahkan.');
    }

    public function edit(SteamerStandard $steamerStandard)
    {
        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('steamer_standards.edit', compact('steamerStandard', 'products'));
    }

    public function update(Request $request, SteamerStandard $steamerStandard)
    {
        $validated = $request->validate([
            'product_uuid' => [
                'required',
                'uuid',
                Rule::unique('steamer_standards')
                    ->where(fn ($q) => $q->where('area_uuid', Auth::user()->area_uuid))
                    ->ignore($steamerStandard->uuid, 'uuid'),
            ],
            'room_temp_min' => 'nullable|numeric',
            'room_temp_max' => 'nullable|numeric|gte:room_temp_min',
            'setup_time_min' => 'nullable|integer|min:0',
            'setup_time_max' => 'nullable|integer|gte:setup_time_min',
            'core_temp_min' => 'nullable|numeric',
            'core_temp_max' => 'nullable|numeric|gte:core_temp_min',
        ]);

        $steamerStandard->update($validated);

        return redirect()->route('steamer-standards.index')
            ->with('success', 'Standar steamer berhasil diperbarui.');
    }

    public function destroy(SteamerStandard $steamerStandard)
    {
        $steamerStandard->delete();

        return redirect()->route('steamer-standards.index')
            ->with('success', 'Standar steamer berhasil dihapus.');
    }
}