<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportFragileItem;
use App\Models\DetailFragileItem;
use App\Models\FragileItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ReportFragileItemController extends Controller
{
    public function index()
    {
        $reports = ReportFragileItem::latest()->paginate(10);
        return view('report_fragile_item.index', compact('reports'));
    }

    public function create()
    {
        $fragileItems = FragileItem::orderBy('section_name')->get();
        return view('report_fragile_item.create', compact('fragileItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            // 'created_by' => 'required|string',
        ]);

        $report = ReportFragileItem::create([
            'uuid' => Str::uuid(),
            'area_uuid' => Auth::user()->area_uuid,
            'date' => $request->date,
            'shift' => $request->shift,
            'created_by' => Auth::user()->name,
            'known_by' => $request->known_by,
            'approved_by' => $request->approved_by,
        ]);

        foreach ($request->items as $data) {
            DetailFragileItem::create([
                'uuid' => Str::uuid(),
                'report_fragile_item_uuid' => $report->uuid,
                'fragile_item_uuid' => $data['fragile_item_uuid'],
                'time_start' => $data['time_start'] ?? null,
                'time_end' => $data['time_end'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil disimpan.');
    }

    public function destroy($uuid)
    {
        $report = ReportFragileItem::where('uuid', $uuid)->firstOrFail();
        $report->delete();
        return redirect()->route('report-fragile-item.index')->with('success', 'Laporan berhasil dihapus.');
    }
}