<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FragileItem;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FragileItemController extends Controller
{
    public function index()
    {
        $fragileItems = FragileItem::with('area')->latest()->paginate(10);
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
}