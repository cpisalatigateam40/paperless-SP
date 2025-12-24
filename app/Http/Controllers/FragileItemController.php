<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FragileItem;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Exports\FragileItemTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FragileItemImport;

class FragileItemController extends Controller
{
    public function index(Request $request)
    {
        $query = FragileItem::with('area')
            ->orderBy('item_name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                ->orWhere('section_name', 'like', "%{$search}%")
                ->orWhere('owner', 'like', "%{$search}%");
            });
        }

        $fragileItems = $query->paginate(10)->withQueryString();

        return view('fragile_item.index', compact('fragileItems'));
    }

    public function create()
    {
        return view('fragile_item.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'section_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'owner' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $areaUuid = $user->area_uuid;

        FragileItem::create([
            'uuid' => Str::uuid(),
            'item_name' => $request->item_name,
            'section_name' => $request->section_name,
            'quantity' => $request->quantity,
            'owner' => $request->owner,
            'area_uuid' => $areaUuid,
        ]);

        return redirect()->route('fragile-item.index')->with('success', 'Item created successfully.');
    }

    public function edit(string $uuid)
    {
        $fragileItem = FragileItem::where('uuid', $uuid)->firstOrFail();
        return view('fragile_item.edit', compact('fragileItem'));
    }

    public function update(Request $request, string $uuid)
    {
        $fragileItem = FragileItem::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'item_name' => 'required|string|max:255',
            'section_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'owner' => 'nullable|string|max:255',
        ]);

        $fragileItem->update($request->only(['item_name', 'section_name', 'quantity', 'owner']));

        return redirect()->route('fragile-item.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $fragileItem = FragileItem::where('uuid', $uuid)->firstOrFail();
        $fragileItem->delete();

        return redirect()->route('fragile-item.index')->with('success', 'Item deleted successfully.');
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new FragileItemTemplateExport,
            'template-fragile-item.xlsx'
        );
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new FragileItemImport, $request->file('file'));

        return redirect()
            ->route('fragile-item.index')
            ->with('success', 'Data fragile item berhasil diimport');
    }
}