<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Area;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with('area')->paginate(10);
        return view('section.section', compact('sections'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('section.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'area_uuid' => 'nullable|exists:areas,uuid',
        ]);

        Section::create($request->only('section_name', 'area_uuid'));

        return redirect()->route('sections.index')->with('success', 'Section berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $section = Section::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        return view('section.edit', compact('section', 'areas'));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'area_uuid' => 'nullable|exists:areas,uuid',
        ]);

        $section = Section::where('uuid', $uuid)->firstOrFail();
        $section->update($request->only('section_name', 'area_uuid'));

        return redirect()->route('sections.index')->with('success', 'Section berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $section = Section::where('uuid', $uuid)->firstOrFail();
        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section berhasil dihapus.');
    }
}