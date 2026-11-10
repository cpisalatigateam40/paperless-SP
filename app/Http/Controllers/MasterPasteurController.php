<?php

namespace App\Http\Controllers;

use App\Models\MasterPasteur;
use App\Models\MasterPasteurStep;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterPasteurController extends Controller
{
    public function index()
    {
        $masters = MasterPasteur::with(['product', 'steps'])
            ->latest()
            ->paginate(20);

        return view('master_pasteurs.index', compact('masters'));
    }

    public function create()
    {
        // Hanya produk yang belum punya master
        $used = MasterPasteur::pluck('product_uuid');
        $products = Product::uniqueByName()->whereNotIn('uuid', $used);

        return view('master_pasteurs.create', [
            'products' => $products,
            'steps'    => MasterPasteurStep::STEPS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));

        DB::transaction(fn () => $this->persist(
            ['area_uuid' => Auth::user()->area_uuid, 'product_uuid' => $data['product_uuid']],
            $data
        ));

        return redirect()->route('master_pasteurs.index')
            ->with('success', 'Master pasteurisasi berhasil ditambahkan');
    }

    public function edit(MasterPasteur $master_pasteur)
    {
        $master_pasteur->load(['product', 'steps']);

        return view('master_pasteurs.edit', [
            'master' => $master_pasteur,
            'steps'  => MasterPasteurStep::STEPS,
            // keyed by step_order supaya gampang dipanggil di Blade
            'stepValues' => $master_pasteur->steps->keyBy('step_order'),
        ]);
    }

    public function update(Request $request, MasterPasteur $master_pasteur)
    {
        $data = $request->validate($this->rules($request, $master_pasteur));

        DB::transaction(fn () => $this->persist(
            ['uuid' => $master_pasteur->uuid],
            $data
        ));

        return redirect()->route('master_pasteurs.index')
            ->with('success', 'Master pasteurisasi berhasil diperbarui');
    }

    public function destroy(MasterPasteur $master_pasteur)
    {
        DB::transaction(function () use ($master_pasteur) {
            $master_pasteur->steps()->delete();
            $master_pasteur->delete();
        });

        return redirect()->route('master_pasteurs.index')
            ->with('success', 'Master pasteurisasi berhasil dihapus');
    }

    /**
     * JSON untuk autofill form laporan (dipakai di tahap perombakan form).
     */
    public function byProduct(string $productUuid)
    {
        $master = MasterPasteur::with('steps')
            ->where('product_uuid', $productUuid)
            ->first();

        if (!$master) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'            => true,
            'initial_temp_min' => $master->initial_temp_min,
            'initial_temp_max' => $master->initial_temp_max,
            'steps'            => $master->steps->keyBy('step_order')->map(fn ($s) => [
                'step_name'      => $s->step_name,
                'set_time'       => $s->set_time,
                'water_temp_min' => $s->water_temp_min,
                'water_temp_max' => $s->water_temp_max,
                'pressure_min'   => $s->pressure_min,
                'pressure_max'   => $s->pressure_max,
            ]),
        ]);
    }

    /* ---------------- private ---------------- */

    private function rules(Request $request, ?MasterPasteur $master = null): array
    {
        $rules = [
            'initial_temp_min'       => 'nullable|numeric|required_with:initial_temp_max',
            'initial_temp_max'       => 'nullable|numeric|gte:initial_temp_min',
            'steps'                  => 'required|array',
            'steps.*.set_time'       => 'nullable|numeric|min:0',
            'steps.*.water_temp_min' => 'nullable|numeric|required_with:steps.*.water_temp_max',
            'steps.*.water_temp_max' => 'nullable|numeric|gte:steps.*.water_temp_min',
            'steps.*.pressure_min'   => 'nullable|numeric|min:0|required_with:steps.*.pressure_max',
            'steps.*.pressure_max'   => 'nullable|numeric|gte:steps.*.pressure_min',
        ];

        // Produk hanya divalidasi saat create (tidak bisa diganti saat edit)
        if (!$master) {
            $rules['product_uuid'] = [
                'required',
                Rule::unique('master_pasteurs', 'product_uuid')
                    ->where('area_uuid', Auth::user()->area_uuid),
            ];
        }

        return $rules;
    }

    private function persist(array $key, array $data): void
    {
        $master = MasterPasteur::updateOrCreate(
            $key,
            Arr::only($data, ['initial_temp_min', 'initial_temp_max'])
        );

        foreach (MasterPasteurStep::STEPS as $order => $name) {
            $row = $data['steps'][$order] ?? [];

            $master->steps()->updateOrCreate(
                ['step_order' => $order],
                ['step_name' => $name] + Arr::only($row, [
                    'set_time', 'water_temp_min', 'water_temp_max',
                    'pressure_min', 'pressure_max',
                ])
            );
        }
    }
}