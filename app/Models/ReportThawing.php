<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportThawing extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_thawings';

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

    protected $auditEvents = [
        'updated',
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
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'package_condition' => ['bad' => ['sobek'], 'ok' => 'utuh'],
                'room_condition'    => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'product_condition' => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailThawing::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const PACKAGE_CONDITIONS = ['utuh', 'sobek'];
    public const CHECK_OPTIONS      = ['OK', 'Tidak OK'];

    // Peta "batch upload + (tanggal|shift)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $rawMaterialCache = null;

    // SAMAKAN query dan urutannya dengan ReportThawingController@create
    private static function rawMaterialOptions(): array
    {
        if (self::$rawMaterialCache === null) {
            self::$rawMaterialCache = RawMaterial::orderBy('material_name')->get()
                ->map(fn ($m) => ['name' => $m->material_name, 'uuid' => $m->uuid])
                ->all();
        }
        return self::$rawMaterialCache;
    }

    public static function getImportMapping(): array
    {
        return [
            // Laporan (kolom yang sama di semua baris satu laporan)
            'Tanggal'           => 'date',
            'Shift'             => 'shift',
            // Detail
            'Bahan Baku'        => 'raw_material_uuid',
            'Qty'               => 'qty',
            'Kode Produksi'     => 'production_code',
            'Kondisi Kemasan'   => 'package_condition',
            'Mulai Thawing'     => 'start_thawing_time',
            'Selesai Thawing'   => 'end_thawing_time',
            'Jam Pemeriksaan'   => 'inspection_time',
            'Kondisi Ruangan'   => 'room_condition',
            'Suhu Ruangan (°C)' => 'room_temp',
            'Suhu Air (°C)'     => 'water_temp',
            'Suhu Produk (°C)'  => 'product_temp',
            'Kondisi Produk'    => 'product_condition',
        ];
    }

    public static function getImportRules(): array
    {
        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'  => 'required|date',
            'shift' => 'required|string|max:50',

            // detail (dari Excel); hanya bahan baku yang wajib, sama seperti store()
            'raw_material_uuid'  => 'required',
            'qty'                => 'nullable',
            'production_code'    => 'nullable|string|max:100',
            'package_condition'  => ['nullable', Rule::in(self::PACKAGE_CONDITIONS)],
            'start_thawing_time' => 'nullable|date_format:H:i',
            'end_thawing_time'   => 'nullable|date_format:H:i',
            'inspection_time'    => 'nullable|date_format:H:i',
            'room_condition'     => ['nullable', Rule::in(self::CHECK_OPTIONS)],
            'room_temp'          => 'nullable|numeric',
            'water_temp'         => 'nullable|numeric',
            'product_temp'       => 'nullable|numeric',
            'product_condition'  => ['nullable', Rule::in(self::CHECK_OPTIONS)],
        ];
    }

    public static function getMasterDataFields(): array
    {
        return [
            'raw_material_uuid' => [
                'label'    => 'Bahan Baku',
                'callback' => fn () => array_column(self::rawMaterialOptions(), 'name'),
            ],
            'package_condition' => ['label' => 'Kondisi Kemasan', 'options' => self::PACKAGE_CONDITIONS],
            'room_condition'    => ['label' => 'Kondisi Ruangan', 'options' => self::CHECK_OPTIONS],
            'product_condition' => ['label' => 'Kondisi Produk', 'options' => self::CHECK_OPTIONS],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        return [[
            'date'               => now()->toDateString(),
            'shift'              => self::defaultShift(),
            'raw_material_uuid'  => self::rawMaterialOptions()[0]['name'] ?? null,
            'qty'                => 10,
            'production_code'    => 'CONTOH-001',
            'package_condition'  => 'utuh',
            'start_thawing_time' => '08:00',
            'end_thawing_time'   => '10:00',
            'inspection_time'    => '08:30',
            'room_condition'     => 'OK',
            'room_temp'          => 25.0,
            'water_temp'         => 20.0,
            'product_temp'       => 5.0,
            'product_condition'  => 'OK',
        ]];
    }

    public static function transformImportRow(array $row): array
    {
        // Tanggal
        if (array_key_exists('date', $row)) {
            $date = self::toDate($row['date']);
            if ($date === null) {
                throw new \RuntimeException('Tanggal tidak valid (contoh: 2026-10-27).');
            }
            $row['date'] = $date;
        }

        // Shift: kosong -> session (QC Inspector) atau NON-SHIFT
        $shift = isset($row['shift']) ? trim((string) $row['shift']) : '';
        $row['shift'] = $shift !== '' ? $shift : self::defaultShift();

        // Bahan baku: nama -> uuid (tolak bila tidak ada / ganda)
        if (isset($row['raw_material_uuid'])) {
            $name  = trim((string) $row['raw_material_uuid']);
            $match = array_values(array_filter(
                self::rawMaterialOptions(),
                fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($name)
            ));

            if (count($match) === 0) {
                throw new \RuntimeException("Bahan \"{$name}\" tidak ditemukan.");
            }
            if (count($match) > 1) {
                throw new \RuntimeException("Bahan \"{$name}\" ganda, tidak bisa dibedakan.");
            }
            $row['raw_material_uuid'] = $match[0]['uuid'];
        }

        // Jam: serial Excel / teks -> "H:i"
        $timeLabels = [
            'start_thawing_time' => 'Mulai Thawing',
            'end_thawing_time'   => 'Selesai Thawing',
            'inspection_time'    => 'Jam Pemeriksaan',
        ];
        foreach ($timeLabels as $key => $label) {
            if (array_key_exists($key, $row)) {
                $time = self::toHi($row[$key]);
                if ($time === null) {
                    throw new \RuntimeException("{$label} tidak valid.");
                }
                $row[$key] = $time;
            }
        }

        // Angka bertulis koma desimal ("25,5") -> titik
        foreach (['qty', 'room_temp', 'water_temp', 'product_temp'] as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        if (isset($row['production_code'])) {
            $row['production_code'] = trim((string) $row['production_code']);
        }

        // Kondisi kemasan: samakan huruf besar/kecil dengan daftar resmi
        if (isset($row['package_condition'])) {
            $v = mb_strtolower(trim((string) $row['package_condition']));
            foreach (self::PACKAGE_CONDITIONS as $opt) {
                if (mb_strtolower($opt) === $v) {
                    $row['package_condition'] = $opt;
                    break;
                }
            }
        }

        // Kondisi ruangan / produk: OK / Tidak OK
        foreach (['room_condition', 'product_condition'] as $key) {
            if (isset($row[$key])) {
                $v = mb_strtolower(trim((string) $row[$key]));
                $row[$key] = match (true) {
                    $v === 'ok'                                  => 'OK',
                    in_array($v, ['tidak ok', 'nok', 'x'], true) => 'Tidak OK',
                    default                                      => trim((string) $row[$key]),
                };
            }
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift dalam SATU upload
        $key  = $row['date'] . '|' . $row['shift'];
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'created_by' => $row['created_by'],
            ]
        );

        DetailThawing::create([
            'uuid'               => (string) Str::uuid(),
            'report_uuid'        => $report->uuid,
            'raw_material_uuid'  => $row['raw_material_uuid'],
            'qty'                => $row['qty'] ?? null,
            'production_code'    => $row['production_code'] ?? null,
            'package_condition'  => $row['package_condition'] ?? null,
            'start_thawing_time' => $row['start_thawing_time'] ?? null,
            'end_thawing_time'   => $row['end_thawing_time'] ?? null,
            'inspection_time'    => $row['inspection_time'] ?? null,
            'room_condition'     => $row['room_condition'] ?? null,
            'room_temp'          => $row['room_temp'] ?? null,
            'water_temp'         => $row['water_temp'] ?? null,
            'product_temp'       => $row['product_temp'] ?? null,
            'product_condition'  => $row['product_condition'] ?? null,
        ]);
    }
}
