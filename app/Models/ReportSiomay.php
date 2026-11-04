<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Validation\Rule;

class ReportSiomay extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_siomays';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'production_code',
        'date',
        'shift',
        'start_time',
        'end_time',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'gramase',
        'is_audit',
        'source_uuid',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'is_audit' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
        static::addGlobalScope(new UserAreaScope);
    }

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
                'details' => [
                    'rawMaterials' => [],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        // aktual harus sama dengan target; hanya jika keduanya terisi
        $tempDiffers = fn ($row) =>
            $row->target_temperature !== null && $row->target_temperature !== ''
            && $row->actual_temperature !== null && $row->actual_temperature !== ''
            && abs((float) $row->actual_temperature - (float) $row->target_temperature) > 0.0001;

        return [
            'details' => [
                'color'   => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'aroma'   => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'taste'   => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'texture' => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'actual_temperature' => [
                    'when' => $tempDiffers,
                    'ok'   => fn ($row) => $row->target_temperature,
                ],
            ],
            'details.rawMaterials' => [
                'sensory' => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
            ],
        ];
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Produk utama
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    // Relasi ke Detail Proses
    public function details()
    {
        return $this->hasMany(DetailSiomay::class, 'report_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const OK_NOK = ['OK', 'Tidak OK'];

    // Kolom laporan dan proses yang hanya boleh diisi di baris pertama tiap kelompok
    public const PROCESS_KEYS = [
        'gramase', 'start_time', 'end_time', 'time', 'process_step', 'color', 'aroma', 'taste',
        'texture', 'mixing_paddle', 'duration', 'pressure', 'target_temperature',
        'actual_temperature', 'notes',
    ];

    // Peta "batch upload" => uuid laporan dan detail, supaya baris sekelompok masuk induk yang sama
    private static array $importUuids = [];

    private static ?array $productCache = null;
    private static ?array $rawCache     = null;

    // SAMAKAN query dengan controller yang mengirim $products ke form create
    private static function productOptions(): array
    {
        if (self::$productCache === null) {
            self::$productCache = Product::uniqueByName()
                ->map(fn ($p) => ['name' => $p->product_name, 'uuid' => $p->uuid])
                ->all();
        }
        return self::$productCache;
    }

    // SAMAKAN query dengan controller yang mengirim $rawMaterials ke form create
    private static function rawOptions(): array
    {
        if (self::$rawCache === null) {
            self::$rawCache = RawMaterial::orderBy('material_name')->get()
                ->map(fn ($m) => ['name' => $m->material_name, 'uuid' => $m->uuid])
                ->all();
        }
        return self::$rawCache;
    }

    // Nama -> uuid; lempar exception bila tidak ada / ganda
    private static function resolveByName(array $options, string $name, string $label): string
    {
        $name  = trim($name);
        $match = array_values(array_filter(
            $options,
            fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($name)
        ));

        if (count($match) === 0) {
            throw new \RuntimeException("{$label} \"{$name}\" tidak ditemukan.");
        }
        if (count($match) > 1) {
            throw new \RuntimeException("{$label} \"{$name}\" ganda, tidak bisa dibedakan.");
        }
        return $match[0]['uuid'];
    }

    private static function hasProcessData(array $row): bool
    {
        foreach (self::PROCESS_KEYS as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) {
                return true;
            }
        }
        return false;
    }

    public static function getImportMapping(): array
    {
        return [
            // Kunci kelompok (diulang di setiap baris satu laporan)
            'Tanggal'                => 'date',
            'Shift'                  => 'shift',
            'Produk'                 => 'product_uuid',
            'Kode Produksi'          => 'production_code',
            // Laporan (isi di baris pertama tiap kelompok)
            'Gramase'                => 'gramase',
            'Waktu Start'            => 'start_time',
            'Waktu Stop'             => 'end_time',
            // Proses (isi di baris pertama tiap kelompok)
            'Pukul'                  => 'time',
            'Tahapan Proses'         => 'process_step',
            'Warna'                  => 'color',
            'Aroma'                  => 'aroma',
            'Rasa'                   => 'taste',
            'Tekstur'                => 'texture',
            'Mixing Paddle'          => 'mixing_paddle',
            'Lama Proses (menit)'    => 'duration',
            'Pressure (Bar)'         => 'pressure',
            'Target Temperature (°C)' => 'target_temperature',
            'Actual Temperature (°C)' => 'actual_temperature',
            'Catatan'                => 'notes',
            // Bahan baku (satu per baris)
            'Bahan Baku'             => 'raw_material_uuid',
            'Berat (kg)'             => 'amount',
            'Sensory Bahan Baku'     => 'sensory',
        ];
    }

    public static function getImportRules(): array
    {
        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // kunci kelompok (dari Excel)
            'date'            => 'required|date',
            'shift'           => 'required|string|max:50',
            'product_uuid'    => 'required',
            'production_code' => 'nullable|string|max:100',

            // laporan dan proses (wajib hanya di baris pertama; dicek di importRow)
            'gramase'            => 'nullable|numeric|min:0',
            'start_time'         => 'nullable|date_format:H:i',
            'end_time'           => 'nullable|date_format:H:i',
            'time'               => 'nullable|date_format:H:i',
            'process_step'       => 'nullable|string',
            'color'              => ['nullable', Rule::in(self::OK_NOK)],
            'aroma'              => ['nullable', Rule::in(self::OK_NOK)],
            'taste'              => ['nullable', Rule::in(self::OK_NOK)],
            'texture'            => ['nullable', Rule::in(self::OK_NOK)],
            'mixing_paddle'      => 'nullable|in:on,off',
            'duration'           => 'nullable|numeric',
            'pressure'           => 'nullable|numeric',
            'target_temperature' => 'nullable|numeric',
            'actual_temperature' => 'nullable|numeric',
            'notes'              => 'nullable|string',

            // bahan baku (dari Excel)
            'raw_material_uuid' => 'required',
            'amount'            => 'nullable|numeric|min:0',
            'sensory'           => ['nullable', Rule::in(self::OK_NOK)],
        ];
    }

    public static function getMasterDataFields(): array
    {
        $okNok = ['options' => self::OK_NOK];

        return [
            'product_uuid' => [
                'label'    => 'Produk',
                'callback' => fn () => array_column(self::productOptions(), 'name'),
            ],
            'raw_material_uuid' => [
                'label'    => 'Bahan Baku',
                'callback' => fn () => array_column(self::rawOptions(), 'name'),
            ],
            'color'         => ['label' => 'Warna'] + $okNok,
            'aroma'         => ['label' => 'Aroma'] + $okNok,
            'taste'         => ['label' => 'Rasa'] + $okNok,
            'texture'       => ['label' => 'Tekstur'] + $okNok,
            'sensory'       => ['label' => 'Sensory Bahan Baku'] + $okNok,
            'mixing_paddle' => ['label' => 'Mixing Paddle', 'options' => ['On', 'Off']],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        return [[
            'date'               => now()->toDateString(),
            'shift'              => self::defaultShift(),
            'product_uuid'       => self::productOptions()[0]['name'] ?? null,
            'production_code'    => 'CONTOH-001',
            'gramase'            => 100,
            'start_time'         => '08:00',
            'end_time'           => '09:00',
            'time'               => '08:15',
            'process_step'       => 'Contoh tahapan proses',
            'color'              => 'OK',
            'aroma'              => 'OK',
            'taste'              => 'OK',
            'texture'            => 'OK',
            'mixing_paddle'      => 'On',
            'duration'           => 30,
            'pressure'           => 1.5,
            'target_temperature' => 80,
            'actual_temperature' => 80,
            'notes'              => 'Contoh pengisian, timpa atau hapus baris ini',
            'raw_material_uuid'  => self::rawOptions()[0]['name'] ?? null,
            'amount'             => 5,
            'sensory'            => 'OK',
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

        // Produk dan bahan baku: nama -> uuid
        if (isset($row['product_uuid'])) {
            $row['product_uuid'] = self::resolveByName(self::productOptions(), (string) $row['product_uuid'], 'Produk');
        }
        if (isset($row['raw_material_uuid'])) {
            $row['raw_material_uuid'] = self::resolveByName(self::rawOptions(), (string) $row['raw_material_uuid'], 'Bahan');
        }

        // Jam: serial Excel / teks -> "H:i"
        foreach (['start_time' => 'Waktu Start', 'end_time' => 'Waktu Stop', 'time' => 'Pukul'] as $key => $label) {
            if (array_key_exists($key, $row)) {
                $time = self::toHi($row[$key]);
                if ($time === null) {
                    throw new \RuntimeException("{$label} tidak valid.");
                }
                $row[$key] = $time;
            }
        }

        // Angka bertulis koma desimal ("30,5") -> titik
        foreach (['gramase', 'duration', 'pressure', 'target_temperature', 'actual_temperature', 'amount'] as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        // OK / Tidak OK
        foreach (['color', 'aroma', 'taste', 'texture', 'sensory'] as $key) {
            if (isset($row[$key])) {
                $v = mb_strtolower(trim((string) $row[$key]));
                $row[$key] = match (true) {
                    in_array($v, ['ok', '✓', '√', '✔'], true)    => 'OK',
                    in_array($v, ['tidak ok', 'nok', 'x'], true) => 'Tidak OK',
                    default                                      => trim((string) $row[$key]),
                };
            }
        }

        // Mixing paddle: On / Off
        if (isset($row['mixing_paddle'])) {
            $v = mb_strtolower(trim((string) $row['mixing_paddle']));
            if (!in_array($v, ['on', 'off'], true)) {
                throw new \RuntimeException('Mixing Paddle harus On atau Off.');
            }
            $row['mixing_paddle'] = $v;
        }

        // Teks yang bisa terbaca sebagai angka oleh Excel
        foreach (['production_code', 'process_step', 'notes'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        $batch = $row['import_batch'];
        $key   = implode('|', [$row['date'], $row['shift'], $row['product_uuid'], $row['production_code'] ?? '']);

        // Satu laporan dan satu detail per kelompok dalam SATU upload
        $reportUuid = self::$importUuids[$batch]['report'][$key] ??= (string) Str::uuid();
        $detailUuid = self::$importUuids[$batch]['detail'][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $reportUuid],
            [
                'area_uuid'       => $row['area_uuid'],
                'product_uuid'    => $row['product_uuid'],
                'production_code' => $row['production_code'] ?? null,
                'date'            => $row['date'],
                'shift'           => $row['shift'],
                'start_time'      => $row['start_time'] ?? null,
                'end_time'        => $row['end_time'] ?? null,
                'gramase'         => $row['gramase'] ?? null,
                'created_by'      => $row['created_by'],
            ]
        );

        $firstRowMessage = 'Baris pertama tiap kelompok (Tanggal, Shift, Produk, Kode Produksi) wajib mengisi Gramase dan Pukul.';

        if ($report->wasRecentlyCreated && !isset($row['gramase'])) {
            throw new \RuntimeException($firstRowMessage);
        }

        $detail = DetailSiomay::firstOrCreate(
            ['uuid' => $detailUuid],
            [
                'report_uuid'        => $report->uuid,
                'time'               => $row['time'] ?? null,
                'process_step'       => $row['process_step'] ?? null,
                'duration'           => $row['duration'] ?? null,
                'pressure'           => $row['pressure'] ?? null,
                'target_temperature' => $row['target_temperature'] ?? null,
                'actual_temperature' => $row['actual_temperature'] ?? null,
                'color'              => $row['color'] ?? 'OK',
                'aroma'              => $row['aroma'] ?? 'OK',
                'taste'              => $row['taste'] ?? 'OK',
                'texture'            => $row['texture'] ?? 'OK',
                'notes'              => $row['notes'] ?? null,
                'mixing_paddle_on'   => ($row['mixing_paddle'] ?? null) === 'on',
                'mixing_paddle_off'  => ($row['mixing_paddle'] ?? null) === 'off',
            ]
        );

        if ($detail->wasRecentlyCreated) {
            if (!isset($row['time'])) {
                throw new \RuntimeException($firstRowMessage);
            }
        } elseif (self::hasProcessData($row)) {
            throw new \RuntimeException(
                'Data laporan dan proses sudah diisi di baris pertama kelompok ini. Kosongkan kolom Gramase sampai Catatan pada baris ini.'
            );
        }

        // Bahan baku (satu per baris), sama dengan cara simpan di store()
        $detail->rawMaterials()->create([
            'uuid'              => (string) Str::uuid(),
            'raw_material_uuid' => $row['raw_material_uuid'],
            'amount'            => $row['amount'] ?? null,
            'sensory'           => $row['sensory'] ?? 'OK',
        ]);
    }
}