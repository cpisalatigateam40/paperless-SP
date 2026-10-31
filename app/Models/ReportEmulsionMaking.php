<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Traits\HasMaterialImportOptions;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Validation\Rule;

class ReportEmulsionMaking extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyReportToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use HasMaterialImportOptions;

    protected $table = 'report_emulsion_makings';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit',
        'source_uuid',
    ];

    protected $casts = [
        'is_audit' => 'boolean',
    ];

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyReportToAudit();

            $header = $this->header()->withoutGlobalScopes()->first();
            if (!$header) {
                return $clone;
            }

            // Header
            $newHeader = $header->replicate();
            $newHeader->{$this->header()->getForeignKeyName()} = $clone->uuid;
            if (array_key_exists('uuid', $header->getAttributes())) {
                $newHeader->uuid = (string) Str::uuid();
            }
            $newHeader->save();

            // Details & agings: foreign key diambil dari relasi
            foreach (['details', 'agings'] as $relation) {
                $rel = $header->{$relation}();
                $fk = $rel->getForeignKeyName();
                $localKey = $rel->getLocalKeyName();

                foreach ($header->{$relation} as $row) {
                    $copy = $row->replicate();
                    $copy->{$fk} = $newHeader->{$localKey};
                    if (array_key_exists('uuid', $row->getAttributes())) {
                        $copy->uuid = (string) Str::uuid();
                    }
                    $copy->save();
                }
            }

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'header.details' => [
                'conformity' => ['bad' => ['x'], 'ok' => '✓'],
            ],
            'header.agings' => [
                'sensory_color'   => ['bad' => ['x'], 'ok' => '✓'],
                'sensory_texture' => ['bad' => ['x'], 'ok' => '✓'],
                'emulsion_result' => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
            ],
        ];
    }

    protected $auditEvents = [
        'updated',
    ];


    public function header()
    {
        return $this->hasOne(HeaderEmulsionMaking::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const EMULSION_TYPES = ['Emulsi Oil', 'Emulsi Skin', 'Emulsi Gel', 'Emulsi GMB'];

    public static function getImportMapping(): array
    {
        return [
            'Nama Bahan'         => 'material_uuid',
            'Berat (kg)'         => 'weight',
            'Suhu (°C)'          => 'temperature',
            'Kesesuaian Formula' => 'conformity',
        ];
    }

    public static function getImportRules(): array
    {
        return [
            // header (dari getAdditionalImportData di controller)
            'report_uuid'     => 'required',
            'header_uuid'     => 'required',
            'area_uuid'       => 'required',
            'date'            => 'required|date',
            'shift'           => 'required|string|max:50',
            'created_by'      => 'required|string',
            'emulsion_type'   => ['required', Rule::in(self::EMULSION_TYPES)],
            'production_code' => 'nullable|string|max:100',

            // aging (opsional, dari modal; divalidasi lengkap di controller)
            'start_aging'     => 'nullable|date_format:H:i',
            'finish_aging'    => 'nullable|date_format:H:i',
            'sensory_color'   => 'nullable|in:✓,x',
            'sensory_texture' => 'nullable|in:✓,x',
            'temp_after'      => 'nullable|numeric',
            'emulsion_result' => 'nullable|in:OK,Tidak OK',

            // detail bahan baku (dari Excel)
            'material_uuid' => 'required',
            'material_type' => 'required|in:raw,premix',
            'weight'        => 'required|numeric|min:0',
            'temperature'   => 'nullable|numeric',
            'conformity'    => 'required|in:✓,x',
        ];
    }

    public static function getMasterDataFields(): array
    {
        return [
            'material_uuid' => [
                'label'    => 'Bahan Baku',
                'callback' => fn () => array_column(self::materialOptions(), 'label'),
            ],
            'conformity' => ['label' => 'Kesesuaian (✓ / x)', 'options' => ['✓', 'x']],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        return [[
            'material_uuid' => self::materialOptions()[0]['label'] ?? null,
            'weight'        => 10.5,
            'temperature'   => 25.0,
            'conformity'    => '✓',
        ]];
    }

    public static function transformImportRow(array $row): array
    {
        // Bahan: label -> uuid + type
        if (isset($row['material_uuid'])) {
            $m = self::resolveMaterial((string) $row['material_uuid']);
            $row['material_uuid'] = $m['uuid'];
            $row['material_type'] = $m['type'];
        }

        // Angka bertulis koma desimal ("10,5") -> titik
        foreach (['weight', 'temperature'] as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        // Kesesuaian: samakan ke ✓ / x
        if (isset($row['conformity'])) {
            $v = mb_strtolower(trim((string) $row['conformity']));
            $row['conformity'] = match (true) {
                in_array($v, ['✓', '√', '✔', 'v', 'ok'], true) => '✓',
                in_array($v, ['x', '×', 'nok'], true)          => 'x',
                default                                        => $v,
            };
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan + header: dibuat sekali per upload
        $report = static::firstOrCreate(
            ['uuid' => $row['report_uuid']],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'created_by' => $row['created_by'],
            ]
        );

        $header = HeaderEmulsionMaking::firstOrCreate(
            ['uuid' => $row['header_uuid']],
            [
                'report_uuid'     => $report->uuid,
                'emulsion_type'   => $row['emulsion_type'],
                'production_code' => $row['production_code'] ?? null,
            ]
        );

        // Aging: hanya satu per laporan, dan hanya bila diisi di modal
        if (filled($row['start_aging'] ?? null)) {
            AgingEmulsionMaking::firstOrCreate(
                ['header_uuid' => $header->uuid],
                [
                    'uuid'            => (string) Str::uuid(),
                    'start_aging'     => $row['start_aging'],
                    'finish_aging'    => $row['finish_aging'],
                    'sensory_color'   => $row['sensory_color'],
                    'sensory_texture' => $row['sensory_texture'],
                    'temp_after'      => $row['temp_after'] ?? null,
                    'emulsion_result' => $row['emulsion_result'],
                ]
            );
        }

        // Detail bahan baku: sama dengan cara simpan di store()
        $detail = [
            'uuid'        => (string) Str::uuid(),
            'header_uuid' => $header->uuid,
            'weight'      => $row['weight'],
            'temperature' => $row['temperature'] ?? null,
            'conformity'  => $row['conformity'],
        ];

        if ($row['material_type'] === 'raw') {
            $detail['raw_material_uuid'] = $row['material_uuid'];
        } else {
            $detail['material_uuid'] = $row['material_uuid'];
            $detail['material_type'] = 'premix';
        }

        DetailEmulsionMaking::create($detail);
    }
}