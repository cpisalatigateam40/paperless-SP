<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportStorageRmCleanliness;
use App\Models\DetailStorageRmCleanliness;
use App\Models\ItemStorageRmCleanliness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StorageRmCleanlinessController extends Controller
{

    public function index()
    {
        $reports = ReportStorageRmCleanliness::with('details.items')->latest()->paginate(10);
        return view('cleanliness.index', compact('reports'));
    }

    public function create()
    {
        return view('cleanliness.form');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Simpan Report
            $report = ReportStorageRmCleanliness::create([
                'uuid' => Str::uuid(),
                'date' => $request->date,
                'shift' => $request->shift,
                'room_name' => $request->room_name,
                'created_by' => Auth::user()->name,
                'known_by' => $request->known_by,
                'approved_by' => $request->approved_by,
            ]);

            foreach ($request->details as $detailInput) {
                // Simpan Detail
                $detail = DetailStorageRmCleanliness::create([
                    'uuid' => Str::uuid(),
                    'report_uuid' => $report->uuid,
                    'inspection_hour' => $detailInput['inspection_hour'],
                ]);

                foreach ($detailInput['items'] as $itemInput) {
                    // Simpan Item
                    ItemStorageRmCleanliness::create([
                        'detail_uuid' => $detail->uuid,
                        'item' => $itemInput['item'],
                        'condition' => $itemInput['condition'],
                        'notes' => $itemInput['notes'],
                        'corrective_action' => $itemInput['corrective_action'],
                        'verification' => $itemInput['verification'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('cleanliness.index')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}