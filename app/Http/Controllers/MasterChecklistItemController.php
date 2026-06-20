<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\MasterChecklistItem;
use Illuminate\Http\Request;

class MasterChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = MasterChecklistItem::with('area')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return view('master_checklist_items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();

        return view('master_checklist_items.form', compact('areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_uuid' => 'nullable|uuid|exists:areas,uuid',
            'category'  => 'nullable|string|max:255',
            'name'      => 'required|string|max:255',
        ]);

        MasterChecklistItem::create([
            'area_uuid' => $validated['area_uuid'] ?? null,
            'category'  => $validated['category'] ?? null,
            'name'      => $validated['name'],
        ]);

        return redirect()
            ->route('master_checklist_items.index')
            ->with('success', 'Item checklist berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $item = MasterChecklistItem::with('area')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('master_checklist_items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uuid)
    {
        $item = MasterChecklistItem::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();

        return view('master_checklist_items.form', compact('item', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $item = MasterChecklistItem::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'area_uuid' => 'nullable|uuid|exists:areas,uuid',
            'category'  => 'nullable|string|max:255',
            'name'      => 'required|string|max:255',
        ]);

        $item->update([
            'area_uuid' => $validated['area_uuid'] ?? null,
            'category'  => $validated['category'] ?? null,
            'name'      => $validated['name'],
        ]);

        return redirect()
            ->route('master_checklist_items.index')
            ->with('success', 'Item checklist berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $item = MasterChecklistItem::where('uuid', $uuid)->firstOrFail();
        $item->delete();

        return redirect()
            ->route('master_checklist_items.index')
            ->with('success', 'Item checklist berhasil dihapus.');
    }
}