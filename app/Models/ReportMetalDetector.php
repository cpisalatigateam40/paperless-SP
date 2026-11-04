<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Validation\Rule;

class ReportMetalDetector extends Model implements Auditable,ImportableModel, CustomImportRow
{
    protected $table = 'report_metal_detectors';
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'section_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'notes',
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

            foreach ($this->details as $detail) {
                $copy = $detail->replicate();
                if (array_key_exists('uuid', $detail->getAttributes())) {
                    $copy->uuid = (string) Str::uuid();
                }
                $copy->report_uuid = $clone->uuid;
                $copy->save();
            }

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'result_fe'           => ['bad' => ['x'], 'ok' => '√'],
                'result_non_fe'       => ['bad' => ['x'], 'ok' => '√'],
                'result_sus316'       => ['bad' => ['x'], 'ok' => '√'],
                'verif_after_correct' => ['bad' => ['x'], 'ok' => '√'],
            ],
        ];
    }
    

    public function details()
    {
        return $this->hasMany(DetailMetalDetector::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

    /* ===================== IMPORT EXCEL ===================== */
    private static array $importReportUuids = [];

    private static ?array $sectionOptionsCache = null;

    // SAMAKAN query dan urutannya dengan ReportRmArrivalController@create
    private static function sectionOptions(): array
    {
        if (self::$sectionOptionsCache === null) {
            self::$sectionOptionsCache = Section::orderBy('section_name')->get()
                ->map(fn ($s) => ['name' => $s->section_name, 'uuid' => $s->uuid])
                ->all();
        }
        return self::$sectionOptionsCache;
    }

    // Shift cadangan bila kolom Shift di Excel kosong
    private static function defaultShift(): string
    {
        return auth()->user()->hasRole('QC Inspector') && session('shift_number') && session('shift_group')
            ? session('shift_number') . '-' . session('shift_group')
            : 'NON-SHIFT';
    }

    // Serial Excel / DateTime / "2026-10-27" / "27/10/2026" -> "Y-m-d" (atau null)
    private static function toDate($v): ?string
    {
        if ($v === null || $v === '') return null;
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');

        if (is_int($v) || is_float($v) || (is_string($v) && is_numeric($v))) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v)->format('Y-m-d');
        }

        $s = trim((string) $v);
        foreach (['Y-m-d', 'j/n/Y', 'j-n-Y', 'j.n.Y'] as $format) {
            $d   = \DateTime::createFromFormat('!' . $format, $s);
            $err = \DateTime::getLastErrors();
            if ($d && (!$err || ($err['warning_count'] === 0 && $err['error_count'] === 0))) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }

    // Sama dengan ReportMetalDetectorController@create
    private static function productOptions()
    {
        return Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();
    }

    // Peta nama (huruf kecil) => uuid, dibuat sekali per request
    private static ?array $productMap = null;

    private static function productMap(): array
    {
        if (self::$productMap === null) {
            self::$productMap = [];
            foreach (self::productOptions() as $p) {
                self::$productMap[mb_strtolower(trim($p->product_name))] ??= $p->uuid;
            }
        }
        return self::$productMap;
    }

    public static function getImportMapping(): array
    {
        return [
            // Laporan (kolom yang sama di semua baris satu laporan)
            'Tanggal'            => 'date',
            'Shift'              => 'shift',
            'Section'            => 'section_uuid',
            // Detail
            'Produk'             => 'product_uuid',
            'Jam'                => 'hour',
            'Kode Produksi'      => 'production_code',
            'Gramase'            => 'gramase',
            'Fe 1.5 mm'          => 'result_fe',
            'Non Fe 2 mm'        => 'result_non_fe',
            'SUS 316 2.5 mm'     => 'result_sus316',
            'Status'             => 'verif_after_correct',
            'Tindakan Koreksi'   => 'corrective_action',
            'Keterangan'         => 'notes',
            // Notes laporan: isi di baris pertama tiap laporan
            'Notes Laporan'      => 'report_notes',
        ];
    }

    public static function getImportRules(): array
    {
        $areaUuid = auth()->user()->area_uuid;

        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'         => 'required|date',
            'shift'        => 'required|string|max:50',
            'section_uuid' => ['nullable', Rule::exists('sections', 'uuid')->where('area_uuid', $areaUuid)],
            'report_notes' => 'nullable|string',

            // detail (dari Excel)
            'product_uuid'        => ['required', Rule::exists('products', 'uuid')->where('area_uuid', $areaUuid)],
            'hour'                => 'required|date_format:H:i',
            'production_code'     => 'required|string|max:100',
            'gramase'             => 'nullable',
            'result_fe'           => 'required|in:√,x',
            'result_non_fe'       => 'required|in:√,x',
            'result_sus316'       => 'required|in:√,x',
            'verif_after_correct' => 'required|in:√,x',
            'corrective_action'   => 'nullable|string',
            'notes'               => 'nullable|string',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $check = ['label' => 'Hasil (√ / x)', 'options' => ['√', 'x']];

        return [
            'product_uuid' => [
                'label'    => 'Produk',
                'callback' => fn () => self::productOptions()->pluck('product_name')->all(),
            ],
            'result_fe'           => ['label' => 'Fe'] + $check,
            'result_non_fe'       => ['label' => 'Non Fe'] + $check,
            'result_sus316'       => ['label' => 'SUS 316'] + $check,
            'verif_after_correct' => ['label' => 'Verifikasi'] + $check,
            'section_uuid' => [
                'label'    => 'Section',
                'callback' => fn () => array_column(self::sectionOptions(), 'name'),
            ],
        ];
    }

    // Baris contoh di template (baris ke-2), terisi penuh
    public static function getImportPrefillRows(): array
    {
        $first = self::productOptions()->first();

        return [[
            'date'                => now()->toDateString(),
            'shift'               => self::defaultShift(),
            'section_uuid'        => self::sectionOptions()[0]['name'] ?? null,
            'product_uuid'        => $first?->product_name,
            'hour'                => '08:30',
            'production_code'     => 'CONTOH-001',
            'gramase'             => 100,
            'result_fe'           => '√',
            'result_non_fe'       => '√',
            'result_sus316'       => '√',
            'verif_after_correct' => '√',
            'corrective_action'   => '-',
            'notes'               => 'Contoh pengisian, timpa atau hapus baris ini',
            'report_notes'        => '',
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

        // Section (opsional): nama -> uuid
        if (isset($row['section_uuid'])) {
            $name  = trim((string) $row['section_uuid']);
            $match = array_values(array_filter(
                self::sectionOptions(),
                fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($name)
            ));

            if (count($match) === 0) {
                throw new \RuntimeException("Section \"{$name}\" tidak ditemukan.");
            }
            if (count($match) > 1) {
                throw new \RuntimeException("Section \"{$name}\" ganda, tidak bisa dibedakan.");
            }
            $row['section_uuid'] = $match[0]['uuid'];
        }

        if (isset($row['report_notes'])) {
            $row['report_notes'] = (string) $row['report_notes'];
        }

        // Produk: nama -> uuid (sama dengan form create: MIN(uuid) per nama)
        if (isset($row['product_uuid'])) {
            $name = trim((string) $row['product_uuid']);
            $uuid = self::productMap()[mb_strtolower($name)] ?? null;

            if (!$uuid) {
                throw new \RuntimeException("Produk \"{$name}\" tidak ditemukan.");
            }
            $row['product_uuid'] = $uuid;
        }

        // Jam: serial Excel / teks -> "H:i"
        if (array_key_exists('hour', $row)) {
            $hour = self::toHi($row['hour']);
            if ($hour === null) {
                throw new \RuntimeException('Jam tidak valid.');
            }
            $row['hour'] = $hour;
        }

        // Hasil: samakan ke √ / x
        foreach (['result_fe', 'result_non_fe', 'result_sus316', 'verif_after_correct'] as $key) {
            if (isset($row[$key])) {
                $v = mb_strtolower(trim((string) $row[$key]));
                $row[$key] = match (true) {
                    in_array($v, ['√', '✓', '✔', 'v', 'ok'], true) => '√',
                    in_array($v, ['x', '×', 'nok'], true)          => 'x',
                    default                                        => $v,
                };
            }
        }

        // Kolom teks yang bisa terbaca sebagai angka oleh Excel
        foreach (['production_code', 'corrective_action', 'notes'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift + section dalam SATU upload
        $key  = implode('|', [$row['date'], $row['shift'], $row['section_uuid'] ?? '']);
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'    => $row['area_uuid'],
                'section_uuid' => $row['section_uuid'] ?? null,
                'date'         => $row['date'],
                'shift'        => $row['shift'],
                'created_by'   => $row['created_by'],
                'notes'        => $row['report_notes'] ?? null,
            ]
        );

        DetailMetalDetector::create([
            'uuid'                => (string) Str::uuid(),
            'report_uuid'         => $report->uuid,
            'product_uuid'        => $row['product_uuid'],
            'hour'                => $row['hour'],
            'production_code'     => $row['production_code'],
            'gramase'             => $row['gramase'] ?? null,
            'result_fe'           => $row['result_fe'],
            'result_non_fe'       => $row['result_non_fe'],
            'result_sus316'       => $row['result_sus316'],
            'corrective_action'   => $row['corrective_action'] ?? null,
            'verif_after_correct' => $row['verif_after_correct'],
            'notes'               => $row['notes'] ?? null,
        ]);
    }

    // "08:30" / "8.30" / serial 0.3541666 / DateTime -> "H:i" (atau null)
    private static function toHi($v): ?string
    {
        if ($v === null || $v === '') return null;
        if ($v instanceof \DateTimeInterface) return $v->format('H:i');
        if (is_int($v) || is_float($v)) {
            $secs = (int) round(fmod((float) $v, 1) * 86400);
            return sprintf('%02d:%02d', intdiv($secs, 3600) % 24, intdiv($secs % 3600, 60));
        }
        if (preg_match('/^(\d{1,2})[:.](\d{2})/', trim((string) $v), $m) && $m[1] < 24 && $m[2] < 60) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }
        return null;
    }
}