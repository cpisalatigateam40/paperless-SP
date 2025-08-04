<?php

namespace App\Http\Controllers;

use App\Models\StandardStuffer;
use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class StandardStufferController extends Controller
{
    public function index()
    {
        $stuffers = StandardStuffer::with(['product', 'area'])->get();
        return view('standard_stuffers.index', compact('stuffers'));
    }

    public function create()
    {
        $products = Product::all();
        $areas = Area::all();

        return view('standard_stuffers.create', compact('products', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_uuid' => 'nullable|exists:products,uuid',
            'long_min' => 'nullable|integer',
            'long_max' => 'nullable|integer',
            'diameter' => 'nullable|integer',
            'weight_min' => 'nullable|integer',
            'weight_max' => 'nullable|integer',
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['area_uuid'] = Auth::user()->area_uuid;

        StandardStuffer::create($validated);

        return redirect()->route('standard-stuffers.index')->with('success', 'Standard stuffer created successfully.');
    }

    public function edit($uuid)
    {
        $stuffer = StandardStuffer::where('uuid', $uuid)->firstOrFail();
        $products = Product::all();
        $areas = Area::all();

        return view('standard_stuffers.edit', compact('stuffer', 'products', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'product_uuid' => 'nullable|exists:products,uuid',
            'long_min' => 'nullable|integer',
            'long_max' => 'nullable|integer',
            'diameter' => 'nullable|integer',
            'weight_min' => 'nullable|integer',
            'weight_max' => 'nullable|integer',
        ]);

        $validated['area_uuid'] = Auth::user()->area_uuid;
        $stuffer = StandardStuffer::where('uuid', $uuid)->firstOrFail();
        $stuffer->update($validated);

        return redirect()->route('standard-stuffers.index')->with('success', 'Standard stuffer updated successfully.');
    }

    public function destroy($uuid)
    {
        $stuffer = StandardStuffer::where('uuid', $uuid)->firstOrFail();
        $stuffer->delete();

        return redirect()->route('standard-stuffers.index')->with('success', 'Standard stuffer deleted successfully.');
    }

    public function import(Request $request)
    {
        // Contoh placeholder import, bisa dikembangkan pakai Excel
        return back()->with('success', 'Import berhasil (dummy).');
    }
}