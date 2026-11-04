<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportMdProduct extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;
    
    protected $table = 'report_md_products';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'metal_detector_uuid',
        'notes',
        'is_audit',
        'source_uuid',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'date' => 'date',
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
                'details' => [
                    'positions' => [],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'status' => ['bad' => [false, 0, '0'], 'ok' => true],
            ],
            'details.positions' => [
                'status' => ['bad' => [false, 0, '0'], 'ok' => true],
            ],
        ];
    }

    public function details()
    {
        return $this->hasMany(DetailMdProduct::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

    public function metalDetector()
    {
        return $this->belongsTo(MetalDetector::class, 'metal_detector_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const TEST_PIECE_LABEL = 'Test Pieces';

    // SESUAIKAN dengan form create: nilai ini yang tersimpan di kolom specimen dan position
    // Nilai yang tersimpan = key di form create
    public const SPECIMENS = [
        'fe_1_5mm'   => 'Fe 1.5 mm',
        'non_fe_2mm' => 'Non Fe 2 mm',
        'sus_2_5mm'  => 'SUS 2.5 mm',
    ];
    public const POSITIONS = [
        'd' => 'Depan',
        't' => 'Tengah',
        'b' => 'Belakang',
    ];

    // Peta "batch upload + (tanggal|shift|metal detector)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $detectorCache = null;
    private static ?array $productCache  = null;

    // key internal => judul kolom, mis. 'pos_0_1' => 'Fe - Tengah'
    private static function positionKeys(): array
    {
        $keys = [];
        foreach (self::SPECIMENS as $specimenKey => $specimenLabel) {
            foreach (self::POSITIONS as $posKey => $posLabel) {
                $keys["pos_{$specimenKey}_{$posKey}"] = "{$specimenLabel} - {$posLabel}";
            }
        }
        return $keys;
    }

    // SAMAKAN query dengan ReportMdProductController@create
    private static function detectorOptions(): array
    {
        if (self::$detectorCache === null) {
            self::$detectorCache = MetalDetector::where('is_active', true)->orderBy('merk')->get()
                ->map(fn ($d) => [
                                        'label' => trim("{$d->merk} - {$d->type_model} ({$d->no_series})"),
                    'uuid'  => $d->uuid,
                ])->all();
        }
        return self::$detectorCache;
    }

    // SAMAKAN query dengan ReportMdProductController@create
    private static function productOptions(): array
    {
        if (self::$productCache === null) {
            self::$productCache = Product::uniqueByName()
                ->map(fn ($p) => ['name' => $p->product_name, 'uuid' => $p->uuid])
                ->all();
        }
        return self::$productCache;
    }

    // ✓ / x / OK / 1 / true -> bool
    private static function toBool($v, string $label): bool
    {
        if (is_bool($v)) return $v;

        $s = mb_strtolower(trim((string) $v));
        if (in_array($s, ['✓', '√', '✔', 'v', 'ok', '1', 'true', 'ya', 'yes'], true)) return true;
        if (in_array($s, ['x', '×', 'nok', 'tidak ok', '0', 'false', 'tidak', 'no'], true)) return false;

        throw new \RuntimeException("Nilai \"{$v}\" pada kolom {$label} tidak valid, gunakan ✓ atau x.");
    }

    public static function getImportMapping(): array
    {
        $columns = [
            // Laporan (kolom yang sama di semua baris satu laporan)
            'Tanggal'        => 'date',
            'Shift'          => 'shift',
            'Metal Detector' => 'metal_detector_uuid',
            // Detail
            'Produk'         => 'product_uuid',
            'Kode Produksi'  => 'production_code',
            'Gramase'        => 'gramase',
            'Best Before'    => 'best_before',
            'Jam'            => 'time',
            'No. Program'    => 'program_number',
        ];

        // Posisi: satu kolom per specimen x posisi
        foreach (self::positionKeys() as $key => $label) {
            $columns[$label] = $key;
        }

        return $columns + [
            'Status'           => 'status',
            'Tindakan Koreksi' => 'corrective_action',
            'Verifikasi'       => 'verification',
            // Notes laporan: isi di baris pertama tiap laporan
            'Notes Laporan'    => 'report_notes',
        ];
    }

    public static function getImportRules(): array
    {
        $areaUuid = auth()->user()->area_uuid;

        $rules = [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'  => 'required|date',
            'shift' => 'required|string|max:50',
            'metal_detector_uuid' => ['required', Rule::exists('metal_detectors', 'uuid')->where('area_uuid', $areaUuid)],
            'report_notes' => 'nullable|string',

            // detail (dari Excel)
            'product_uuid'      => 'nullable',
            'is_test_piece'     => 'boolean',
            'production_code'   => 'nullable|string|max:100',
            'gramase'           => 'required|numeric|min:0',
            'best_before'       => 'nullable|date',
            'time'              => 'nullable|date_format:H:i',
            'program_number'    => 'nullable|string|max:50',
            'status'            => 'boolean',
            'corrective_action' => 'nullable|string',
            'verification'      => 'nullable|string',
        ];

        foreach (array_keys(self::positionKeys()) as $key) {
            $rules[$key] = 'nullable|boolean';
        }

        return $rules;
    }

    public static function getMasterDataFields(): array
    {
        $check = ['options' => ['✓', 'x']];

        $fields = [
            'metal_detector_uuid' => [
                'label'    => 'Metal Detector',
                'callback' => fn () => array_column(self::detectorOptions(), 'label'),
            ],
            'product_uuid' => [
                'label'    => 'Produk',
                'callback' => fn () => array_merge([self::TEST_PIECE_LABEL], array_column(self::productOptions(), 'name')),
            ],
            'status' => ['label' => 'Status'] + $check,
        ];

        foreach (self::positionKeys() as $key => $label) {
            $fields[$key] = ['label' => $label] + $check;
        }

        return $fields;
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        $row = [
            'date'                => now()->toDateString(),
            'shift'               => self::defaultShift(),
            'metal_detector_uuid' => self::detectorOptions()[0]['label'] ?? null,
            'product_uuid'        => self::productOptions()[0]['name'] ?? self::TEST_PIECE_LABEL,
            'production_code'     => 'CONTOH-001',
            'gramase'             => 100,
            'best_before'         => now()->addYear()->toDateString(),
            'time'                => '08:30',
            'program_number'      => '1',
            'status'              => '✓',
            'corrective_action'   => '-',
            'verification'        => '-',
            'report_notes'        => '',
        ];

        foreach (array_keys(self::positionKeys()) as $key) {
            $row[$key] = '✓';
        }

        return [$row];
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

        // Metal detector: label -> uuid (tolak bila tidak ada / ganda)
        if (isset($row['metal_detector_uuid'])) {
            $name  = trim((string) $row['metal_detector_uuid']);
            $match = array_values(array_filter(
                self::detectorOptions(),
                fn ($o) => mb_strtolower(trim($o['label'])) === mb_strtolower($name)
            ));

            if (count($match) === 0) {
                throw new \RuntimeException("Metal detector \"{$name}\" tidak ditemukan.");
            }
            if (count($match) > 1) {
                throw new \RuntimeException("Metal detector \"{$name}\" ganda, tidak bisa dibedakan.");
            }
            $row['metal_detector_uuid'] = $match[0]['uuid'];
        }

        // Produk: "Test Pieces" -> product null + is_test_piece; selain itu nama -> uuid
        $product = isset($row['product_uuid']) ? trim((string) $row['product_uuid']) : '';
        if ($product === '') {
            throw new \RuntimeException('Produk wajib diisi (atau pilih "' . self::TEST_PIECE_LABEL . '").');
        }

        if (mb_strtolower($product) === mb_strtolower(self::TEST_PIECE_LABEL)) {
            $row['product_uuid']  = null;
            $row['is_test_piece'] = true;
        } else {
            $found = collect(self::productOptions())
                ->first(fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($product));

            if (!$found) {
                throw new \RuntimeException("Produk \"{$product}\" tidak ditemukan.");
            }
            $row['product_uuid']  = $found['uuid'];
            $row['is_test_piece'] = false;
        }

        // Jam: serial Excel / teks -> "H:i"
        if (array_key_exists('time', $row)) {
            $time = self::toHi($row['time']);
            if ($time === null) {
                throw new \RuntimeException('Jam tidak valid.');
            }
            $row['time'] = $time;
        }

        // Best before: tanggal
        if (array_key_exists('best_before', $row)) {
            $bb = self::toDate($row['best_before']);
            if ($bb === null) {
                throw new \RuntimeException('Best Before tidak valid (contoh: 2027-10-27).');
            }
            $row['best_before'] = $bb;
        }

        // Teks yang bisa terbaca sebagai angka oleh Excel
        foreach (['production_code', 'program_number', 'corrective_action', 'verification', 'report_notes'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        if (isset($row['gramase']) && is_string($row['gramase'])) {
            $row['gramase'] = str_replace(',', '.', trim($row['gramase']));
        }

        // Status detail: kosong = true (sama dengan store())
        $row['status'] = isset($row['status']) ? self::toBool($row['status'], 'Status') : true;

        // Posisi: ✓ / x -> bool; sel kosong dilewati
        foreach (self::positionKeys() as $key => $label) {
            if (!isset($row[$key])) {
                throw new \RuntimeException("Kolom \"{$label}\" wajib diisi (✓ atau x).");
            }
            $row[$key] = self::toBool($row[$key], $label);
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift + metal detector dalam SATU upload
        $key  = implode('|', [$row['date'], $row['shift'], $row['metal_detector_uuid']]);
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'           => $row['area_uuid'],
                'metal_detector_uuid' => $row['metal_detector_uuid'],
                'date'                => $row['date'],
                'shift'               => $row['shift'],
                'created_by'          => $row['created_by'],
                'notes'               => $row['report_notes'] ?? null,
            ]
        );

        $detail = DetailMdProduct::create([
            'uuid'              => (string) Str::uuid(),
            'report_uuid'       => $report->uuid,
            'product_uuid'      => $row['product_uuid'],
            'is_test_piece'     => $row['is_test_piece'],
            'production_code'   => $row['production_code'] ?? null,
            'gramase'           => $row['gramase'] ?? null,
            'best_before'       => $row['best_before'] ?? null,
            'time'              => $row['time'] ?? null,
            'program_number'    => $row['program_number'] ?? null,
            'corrective_action' => $row['corrective_action'] ?? null,
            'verification'      => $row['verification'] ?? null,
            'status'            => $row['status'],
        ]);

        // Posisi: unpivot kolom specimen x posisi menjadi baris anak
        foreach (self::SPECIMENS as $specimenKey => $specimenLabel) {
            foreach (self::POSITIONS as $posKey => $posLabel) {
                PositionMdProduct::create([
                    'uuid'        => (string) Str::uuid(),
                    'detail_uuid' => $detail->uuid,
                    'specimen'    => $specimenKey,
                    'position'    => $posKey,
                    'status'      => $row["pos_{$specimenKey}_{$posKey}"],
                ]);
            }
        }
    }
}