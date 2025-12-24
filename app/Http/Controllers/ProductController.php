<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductTemplateExport;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('area')
            ->orderBy('product_name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(10)->withQueryString();

        return view('products.index', compact('products'));
    }


    public function create()
    {
        $areas = Area::all();
        return view('products.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'nett_weight' => 'nullable|numeric',
            'shelf_life' => 'nullable|integer',
        ]);

        Product::create([
            'uuid' => Str::uuid(),
            'product_name' => $request->product_name,
            'brand' => $request->brand,
            'nett_weight' => $request->nett_weight,
            'shelf_life' => $request->shelf_life,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit($uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();

        return view('products.edit', compact('product', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'nett_weight' => 'nullable|numeric',
            'shelf_life' => 'nullable|integer',
        ]);

        $product->update([
            'product_name' => $request->product_name,
            'brand' => $request->brand,
            'nett_weight' => $request->nett_weight,
            'shelf_life' => $request->shelf_life,
            'area_uuid' => Auth::user()->area_uuid,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new ProductTemplateExport,
            'template-product.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new ProductImport, $request->file('file'));

        return redirect()
            ->route('products.index')
            ->with('success', 'Data product berhasil diimport');
    }
}