<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\MasterSmokeHouse;
use App\Models\MasterSmokeHouseStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MasterSmokeHouseController extends Controller
{
    public function index()
    {
        $masters = MasterSmokeHouse::with([
            'area',
            'product',
            'steps'
        ])
        ->latest()
        ->paginate(10);

        return view('master-smoke-houses.index', compact('masters'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('master-smoke-houses.create', compact(
            'areas',
            'products'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_uuid' => 'required|exists:products,uuid',
            'machine_name' => 'required|string',
            'remarks' => 'nullable|string',

            'steps' => 'required|array|min:1',

            'steps.*.sequence' => 'required|integer',
            'steps.*.process_name' => 'required|string',
            'steps.*.temperature_min' => 'nullable|numeric',
            'steps.*.temperature_max' => 'nullable|numeric',
            'steps.*.time_minutes' => 'nullable|integer',
            'steps.*.time_minutes_max' => 'nullable|integer',
            'steps.*.rh' => 'nullable|numeric',
            'steps.*.core_temperature' => 'nullable|numeric',
            'steps.*.core_temperature_max' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request) {

            $master = MasterSmokeHouse::create([
                'area_uuid' => Auth::user()->area_uuid,
                'product_uuid' => $request->product_uuid,
                'machine_name' => $request->machine_name,
                'remarks' => $request->remarks,
            ]);

            foreach ($request->steps as $step) {

                MasterSmokeHouseStep::create([
                    'master_uuid' => $master->uuid,
                    'sequence' => $step['sequence'],
                    'process_name' => $step['process_name'],
                    'temperature_min' => $step['temperature_min'] ?: null,
                    'temperature_max' => $step['temperature_max'] ?: null,
                    'time_minutes' => $step['time_minutes'] ?: null,
                    'time_minutes_max' => $step['time_minutes_max'] ?: null,
                    'rh' => $step['rh'] ?: null,
                    'core_temperature' => $step['core_temperature'] ?: null,
                    'core_temperature_max' => $step['core_temperature_max'] ?: null,
                ]);
            }
        });

        return redirect()
            ->route('master-smoke-houses.index')
            ->with('success', 'Master Smoke House berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $master = MasterSmokeHouse::with('steps')
            ->firstWhere('uuid', $uuid);

        abort_if(!$master, 404);

        $areas = Area::orderBy('name')->get();

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();

        return view('master-smoke-houses.edit', compact(
            'master',
            'areas',
            'products'
        ));
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'product_uuid' => 'required|exists:products,uuid',
            'machine_name' => 'required|string',
            'remarks' => 'nullable|string',

            'steps' => 'required|array|min:1',

            'steps.*.sequence' => 'required|integer',
            'steps.*.process_name' => 'required|string',
            'steps.*.temperature_min' => 'nullable|numeric',
            'steps.*.temperature_max' => 'nullable|numeric',
            'steps.*.time_minutes' => 'nullable|integer',
            'steps.*.time_minutes_max' => 'nullable|integer',
            'steps.*.rh' => 'nullable|numeric',
            'steps.*.core_temperature' => 'nullable|numeric',
            'steps.*.core_temperature_max' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($request, $uuid) {

            $master = MasterSmokeHouse::firstWhere('uuid', $uuid);

            abort_if(!$master, 404);

            $master->update([
                'area_uuid' => Auth::user()->area_uuid,
                'product_uuid' => $request->product_uuid,
                'machine_name' => $request->machine_name,
                'remarks' => $request->remarks,
            ]);

            MasterSmokeHouseStep::where(
                'master_uuid',
                $master->uuid
            )->delete();

            foreach ($request->steps as $step) {

                MasterSmokeHouseStep::create([
                    'master_uuid' => $master->uuid,
                    'sequence' => $step['sequence'],
                    'process_name' => $step['process_name'],
                    'temperature_min' => $step['temperature_min'] ?: null,
                    'temperature_max' => $step['temperature_max'] ?: null,
                    'time_minutes' => $step['time_minutes'] ?: null,
                    'time_minutes_max' => $step['time_minutes_max'] ?: null,
                    'rh' => $step['rh'] ?: null,
                    'core_temperature' => $step['core_temperature'] ?: null,
                    'core_temperature_max' => $step['core_temperature_max'] ?: null,
                ]);
            }
        });

        return redirect()
            ->route('master-smoke-houses.index')
            ->with('success', 'Master Smoke House berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $master = MasterSmokeHouse::firstWhere('uuid', $uuid);

        abort_if(!$master, 404);

        $master->delete();

        return redirect()
            ->route('master-smoke-houses.index')
            ->with('success', 'Master Smoke House berhasil dihapus.');
    }

}