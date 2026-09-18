<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\ItemStorageRmCleanliness;
use App\Models\ItemProcessAreaCleanliness;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');

        if ($isSuperadmin) {
            $plants = Area::orderBy('name')->get();
            $plant  = $request->input('plant', $plants->first()->uuid ?? null);
        } else {
            $plants = Area::where('uuid', $user->area_uuid)->get();
            $plant  = $user->area_uuid;
        }

        $date = $request->input('date', now()->toDateString());
        $jam  = $request->input('jam');

        $storageQuery = ItemStorageRmCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
            $q->whereDate('date', $date)
              ->where('area_uuid', $plant);
        });

        $processQuery = ItemProcessAreaCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
            $q->whereDate('date', $date)
              ->where('area_uuid', $plant);
        });

        if ($jam !== null && $jam !== '') {
            $storageQuery->whereHas('detail', function ($q) use ($jam) {
                $q->whereRaw('HOUR(inspection_hour) = ?', [$jam]);
            });
            $processQuery->whereHas('detail', function ($q) use ($jam) {
                $q->whereRaw('HOUR(inspection_hour) = ?', [$jam]);
            });
        }

        $storageVerifications = $storageQuery->pluck('verification');
        $processVerifications = $processQuery->pluck('verification');

        $allVerifications = $storageVerifications->merge($processVerifications);

        $totalPoints = $allVerifications->count();
        $okPoints = $allVerifications->filter(fn($v) => (int) $v === 1)->count();
        $percentage = $totalPoints > 0 ? round(($okPoints / $totalPoints) * 100, 1) : 0;

        $hourOptions = collect(range(0, 23))->map(fn($h) => [
            'value' => $h,
            'label' => sprintf('%02d:00', $h),
        ]);

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $product  = $request->input('product');

        return view('dashboard', compact(
            'percentage', 'okPoints', 'totalPoints',
            'plants', 'hourOptions',
            'plant', 'date', 'jam',
            'isSuperadmin', 'products', 'product'
        ));
    }
}