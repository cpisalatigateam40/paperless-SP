<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Section;
use App\Models\MasterChecklistItem;
use Illuminate\Http\Request;

class MasterChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $query = MasterChecklistItem::with(['area', 'section']);

    // Filter Area (khusus admin & superadmin)
    if (
        auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
        $request->filled('area')
    ) {
        $query->where('area_uuid', $request->area);
    }

    // Filter Section
    if ($request->filled('section')) {
        $query->where('section_uuid', $request->section);
    }

    // Search
    $query->when($request->search, function ($q, $search) {
        $q->where('name', 'like', "%{$search}%");
    });

    $items = $query
        ->orderBy('name')
        ->paginate(20)
        ->withQueryString();

    $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
        ? Area::orderBy('name')->get()
        : collect();

    $sections = auth()->user()->hasAnyRole(['admin', 'superadmin'])
        ? Section::orderBy('section_name')->get()
        : Section::orderBy('section_name')->get(); // UserAreaScope otomatis batasi non-admin

    return view('master_checklist_items.index', compact('items', 'areas', 'sections'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();
        $sections = Section::all();

        return view('master_checklist_items.form', compact('areas', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_uuid'    => 'nullable|uuid|exists:areas,uuid',
            'section_uuid' => 'nullable|uuid|exists:sections,uuid',
            'names'        => 'required|array|min:1',
            'names.*'      => 'required|string|max:255',
        ]);

        foreach ($validated['names'] as $name) {
            MasterChecklistItem::create([
                'area_uuid'    => $validated['area_uuid'] ?? null,
                'section_uuid' => $validated['section_uuid'] ?? null,
                'name'         => $name,
            ]);
        }

        $count = count($validated['names']);

        return redirect()
            ->route('master_checklist_items.index')
            ->with('success', $count > 1 ? "{$count} item checklist berhasil ditambahkan." : 'Item checklist berhasil ditambahkan.');
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
        $sections = Section::all();

        return view('master_checklist_items.form', compact('item', 'areas', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $item = MasterChecklistItem::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'area_uuid'    => 'nullable|uuid|exists:areas,uuid',
            'section_uuid' => 'nullable|uuid|exists:sections,uuid',
            'names'        => 'required|array|min:1',
            'names.0'      => 'required|string|max:255',
            'is_active'    => 'nullable|boolean',
        ]);

        $item->update([
            'area_uuid'    => $validated['area_uuid'] ?? null,
            'section_uuid' => $validated['section_uuid'] ?? null,
            'name'         => $validated['names'][0],
            'is_active'    => $request->boolean('is_active'),
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

        if ($item->detailChangeoverCleanings()->exists()) {
            return redirect()
                ->route('master_checklist_items.index')
                ->with('error', 'Item ini tidak bisa dihapus karena sudah dipakai di laporan Changeover Cleaning.');
        }

        $item->delete();

        return redirect()
            ->route('master_checklist_items.index')
            ->with('success', 'Item checklist berhasil dihapus.');
    }

    public function bySection(string $sectionUuid)
    {
        $items = MasterChecklistItem::where('section_uuid', $sectionUuid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['uuid', 'name']);

        return response()->json($items);
    }

    public function toggleActive(string $uuid)
    {
        $item = MasterChecklistItem::where('uuid', $uuid)->firstOrFail();
        $item->update(['is_active' => !$item->is_active]);

        return redirect()
            ->route('master_checklist_items.index', request()->query())
            ->with('success', $item->is_active
                ? 'Item checklist diaktifkan kembali.'
                : 'Item checklist dinonaktifkan.');
    }
}