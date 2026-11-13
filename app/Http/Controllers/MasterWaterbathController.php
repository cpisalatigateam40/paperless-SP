<?php

namespace App\Http\Controllers;

use App\Models\MasterWaterbath;
use App\Models\MasterWaterbathParameter;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterWaterbathController extends Controller
{
    public function index()
    {
        $masters = MasterWaterbath::with(['product', 'parameters'])
            ->latest()
            ->paginate(20);

        return view('master_waterbaths.index', compact('masters'));
    }

    public function create()
    {
        // Hanya produk (unik per nama) yang belum punya master
        $used     = MasterWaterbath::pluck('product_uuid');
        $products = Product::uniqueByName()->whereNotIn('uuid', $used);

        return view('master_waterbaths.create', [
            'master'     => null,
            'products'   => $products,
            'parameters' => MasterWaterbathParameter::PARAMETERS,
            'values'     => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::transaction(fn () => $this->persist(
            ['area_uuid' => Auth::user()->area_uuid, 'product_uuid' => $data['product_uuid']],
            $data
        ));

        return redirect()->route('master_waterbaths.index')
            ->with('success', 'Master waterbath berhasil ditambahkan');
    }

    public function edit(MasterWaterbath $master_waterbath)
    {
        $master_waterbath->load(['product', 'parameters']);

        return view('master_waterbaths.edit', [
            'master'     => $master_waterbath,
            'products'   => collect(),
            'parameters' => MasterWaterbathParameter::PARAMETERS,
            'values'     => $master_waterbath->parameters->keyBy('parameter'),
        ]);
    }

    public function update(Request $request, MasterWaterbath $master_waterbath)
    {
        $data = $request->validate($this->rules($master_waterbath));

        DB::transaction(fn () => $this->persist(['uuid' => $master_waterbath->uuid], $data));

        return redirect()->route('master_waterbaths.index')
            ->with('success', 'Master waterbath berhasil diperbarui');
    }

    public function destroy(MasterWaterbath $master_waterbath)
    {
        DB::transaction(function () use ($master_waterbath) {
            $master_waterbath->parameters()->delete();
            $master_waterbath->delete();
        });

        return redirect()->route('master_waterbaths.index')
            ->with('success', 'Master waterbath berhasil dihapus');
    }

    /**
     * JSON untuk autofill form laporan (dipakai di tahap perombakan form report).
     */
    public function byProduct(string $productUuid)
    {
        $master = MasterWaterbath::with('parameters')
            ->where('product_uuid', $productUuid)
            ->first();

        if (!$master) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'            => true,
            'initial_temp_min' => $master->initial_temp_min,
            'initial_temp_max' => $master->initial_temp_max,
            'parameters'       => $master->parameters->keyBy('parameter')->map(fn ($p) => [
                'set_temp_min'       => $p->set_temp_min,
                'set_temp_max'       => $p->set_temp_max,
                'cold_zone_temp_min' => $p->cold_zone_temp_min,
                'cold_zone_temp_max' => $p->cold_zone_temp_max,
                'set_time'           => $p->set_time,
                'std_ct_min'         => $p->std_ct_min,
                'std_ct_max'         => $p->std_ct_max,
            ]),
        ]);
    }

    /* ---------------- private ---------------- */

    private function rules(?MasterWaterbath $master = null): array
    {
        $rules = [
            'initial_temp_min' => 'nullable|numeric|required_with:initial_temp_max',
            'initial_temp_max' => 'nullable|numeric|gte:initial_temp_min',

            'parameters'                         => 'required|array',
            'parameters.*.set_temp_min'          => 'nullable|numeric|required_with:parameters.*.set_temp_max',
            'parameters.*.set_temp_max'          => 'nullable|numeric|gte:parameters.*.set_temp_min',
            'parameters.*.set_time'              => 'nullable|numeric|min:0',
            'parameters.*.std_ct_min'            => 'nullable|numeric|required_with:parameters.*.std_ct_max',
            'parameters.*.std_ct_max'            => 'nullable|numeric|gte:parameters.*.std_ct_min',

            // Cold zone khusus drying
            'parameters.drying.cold_zone_temp_min' => 'nullable|numeric|required_with:parameters.drying.cold_zone_temp_max',
            'parameters.drying.cold_zone_temp_max' => 'nullable|numeric|gte:parameters.drying.cold_zone_temp_min',
        ];

        // Produk hanya divalidasi saat create (tidak bisa diganti saat edit)
        if (!$master) {
            $rules['product_uuid'] = [
                'required',
                Rule::unique('master_waterbaths', 'product_uuid')
                    ->where('area_uuid', Auth::user()->area_uuid),
            ];
        }

        return $rules;
    }

    private function persist(array $key, array $data): void
    {
        $master = MasterWaterbath::updateOrCreate(
            $key,
            Arr::only($data, ['initial_temp_min', 'initial_temp_max'])
        );

        foreach (array_keys(MasterWaterbathParameter::PARAMETERS) as $param) {
            $row      = $data['parameters'][$param] ?? [];
            $isDrying = $param === MasterWaterbathParameter::DRYING;

            $values = Arr::only($row, [
                'set_temp_min', 'set_temp_max', 'set_time', 'std_ct_min', 'std_ct_max',
            ]);

            // Cold zone hanya untuk drying; selain itu dipaksa null
            $values['cold_zone_temp_min'] = $isDrying ? ($row['cold_zone_temp_min'] ?? null) : null;
            $values['cold_zone_temp_max'] = $isDrying ? ($row['cold_zone_temp_max'] ?? null) : null;

            $master->parameters()->updateOrCreate(['parameter' => $param], $values);
        }
    }
}