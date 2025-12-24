<?php

namespace App\Imports;

use App\Models\Formula;
use App\Models\Formulation;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Premix;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class FormulaImport implements OnEachRow, WithHeadingRow
{
    /**
     * Cache formula agar 1 formula tidak dibuat berulang
     * key = formula_name + product_uuid
     */
    protected array $formulaCache = [];

    public function onRow(Row $row)
    {
        $data = array_map(
            fn ($v) => is_string($v) ? trim($v) : $v,
            $row->toArray()
        );

        $areaUuid = Auth::user()->area_uuid;

        /**
         * =========================
         * VALIDASI WAJIB
         * =========================
         */
        if (
            empty($data['formula_name']) ||
            empty($data['product_name']) ||
            empty($data['formulation_name'])
        ) {
            // skip baris tidak lengkap
            return;
        }

        /**
         * =========================
         * PRODUCT (AREA BASED)
         * =========================
         */
        $product = Product::where('product_name', $data['product_name'])
            ->where('area_uuid', $areaUuid)
            ->first();

        if (!$product) {
            // produk tidak ditemukan di area user
            return;
        }

        /**
         * =========================
         * FORMULA (CACHE + FIRST OR CREATE)
         * =========================
         */
        $formulaKey = $data['formula_name'] . '-' . $product->uuid;

        if (!isset($this->formulaCache[$formulaKey])) {
            $formula = Formula::firstOrCreate(
                [
                    'area_uuid'    => $areaUuid,
                    'formula_name' => $data['formula_name'],
                    'product_uuid' => $product->uuid,
                ],
                [
                    'uuid'         => (string) Str::uuid(),
                    'product_name' => $product->product_name,
                ]
            );

            $this->formulaCache[$formulaKey] = $formula->uuid;
        }

        $formulaUuid = $this->formulaCache[$formulaKey];

        /**
         * =========================
         * RAW MATERIAL (UPDATE OR CREATE)
         * =========================
         */
        if (!empty($data['raw_material']) && is_numeric($data['raw_weight'])) {
            $raw = RawMaterial::where('material_name', $data['raw_material'])
                ->where('area_uuid', $areaUuid)
                ->first();

            if ($raw) {
                Formulation::updateOrCreate(
                    [
                        'formula_uuid'      => $formulaUuid,
                        'formulation_name'  => $data['formulation_name'],
                        'raw_material_uuid' => $raw->uuid,
                        'premix_uuid'       => null,
                    ],
                    [
                        'uuid'   => (string) Str::uuid(),
                        'weight' => (float) $data['raw_weight'],
                    ]
                );
            }
        }

        /**
         * =========================
         * PREMIX (UPDATE OR CREATE)
         * =========================
         */
        if (!empty($data['premix']) && is_numeric($data['premix_weight'])) {
            $premix = Premix::where('name', $data['premix'])
                ->where('area_uuid', $areaUuid)
                ->first();

            if ($premix) {
                Formulation::updateOrCreate(
                    [
                        'formula_uuid'      => $formulaUuid,
                        'formulation_name'  => $data['formulation_name'],
                        'raw_material_uuid' => null,
                        'premix_uuid'       => $premix->uuid,
                    ],
                    [
                        'uuid'   => (string) Str::uuid(),
                        'weight' => (float) $data['premix_weight'],
                    ]
                );
            }
        }
    }
}
