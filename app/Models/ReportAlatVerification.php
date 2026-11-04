<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;

class ReportAlatVerification extends Model implements ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use ParsesExcelValues;

    protected $table = 'report_alat_verifications';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit', 'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'is_audit' => 'boolean'
    ];

    protected static function booted()
    {
        static::creating(function ($report) {
            if (empty($report->uuid)) {
                $report->uuid = (string) Str::uuid();
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
                'details' => [],
            ]);

            return $clone;
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailAlatVerification::class, 'report_alat_verification_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    // SAMAKAN dengan titikUkurOptions di JavaScript form create
    public const TITIK_UKUR = [
        'scale' => [
            '100 Gr', '200 Gr', '500 Gr', '1000 Gr', '2000 Gr', '5000 Gr',
            '10000 Gr', '15000 Gr', '20000 Gr', '25000 Gr', '50000 Gr',
        ],
        'thermometer' => [
            '-18 °C', '0 °C', '4 °C', '10 °C', '37 °C', '60 °C', '75 °C', '82 °C', '100 °C',
        ],
    ];

    public const KIND_LABELS = ['scale' => 'Timbangan', 'thermometer' => 'Thermometer'];

    // Peta "batch upload + (tanggal|shift)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $alatCache = null;

    // SAMAKAN query dan urutannya dengan controller yang mengirim $scales dan $thermometers ke form create
    private static function alatOptions(): array
    {
        if (self::$alatCache === null) {
            $list = [];

            foreach (Scale::orderBy('type')->orderBy('code')->get() as $s) {
                $list[] = [
                    'label' => '[' . self::KIND_LABELS['scale'] . '] ' . $s->type . ' - ' . $s->code,
                    'uuid'  => $s->uuid,
                    'type'  => 'scale',
                ];
            }
            foreach (Thermometer::orderBy('type')->orderBy('code')->get() as $t) {
                $list[] = [
                    'label' => '[' . self::KIND_LABELS['thermometer'] . '] ' . $t->type . ' - ' . $t->code,
                    'uuid'  => $t->uuid,
                    'type'  => 'thermometer',
                ];
            }

            self::$alatCache = $list;
        }
        return self::$alatCache;
    }

    public static function getImportMapping(): array
    {
        return [
            // Laporan
            'Tanggal'            => 'date',
            'Shift'              => 'shift',
            // Detail
            'Jenis & Kode Alat'  => 'alat',
            'Titik Ukur'         => 'titik_ukur',
            'Nilai Baca (kg/°C)' => 'nilai_baca',
            'Jam Pemeriksaan'    => 'check_time',
            'Catatan'            => 'notes',
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

            // detail (dari Excel)
            'alat_type'  => 'required|in:scale,thermometer',
            'alat_uuid'  => 'required',
            'titik_ukur' => 'required|string',
            'nilai_baca' => 'required|numeric',
            'check_time' => 'nullable|date_format:H:i',
            'notes'      => 'nullable|string',
        ];
    }

    public static function getMasterDataFields(): array
    {
        return [
            'alat' => [
                'label'    => 'Jenis & Kode Alat',
                'callback' => fn () => array_column(self::alatOptions(), 'label'),
            ],
            'titik_ukur' => [
                'label'    => 'Titik Ukur',
                'callback' => fn () => array_values(array_unique(
                    array_merge(self::TITIK_UKUR['scale'], self::TITIK_UKUR['thermometer'])
                )),
            ],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        $first = self::alatOptions()[0] ?? null;

        return [[
            'date'       => now()->toDateString(),
            'shift'      => self::defaultShift(),
            'alat'       => $first['label'] ?? null,
            'titik_ukur' => $first ? (self::TITIK_UKUR[$first['type']][0] ?? null) : null,
            'nilai_baca' => 100,
            'check_time' => '08:00',
            'notes'      => 'Contoh pengisian, timpa atau hapus baris ini',
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

        // Alat: label berawalan jenis -> uuid + type (tolak bila tidak ada / ganda)
        $label = trim((string) ($row['alat'] ?? ''));
        if ($label === '') {
            throw new \RuntimeException('Jenis & Kode Alat wajib diisi.');
        }

        $match = array_values(array_filter(
            self::alatOptions(),
            fn ($o) => mb_strtolower(trim($o['label'])) === mb_strtolower($label)
        ));

        if (count($match) === 0) {
            throw new \RuntimeException("Alat \"{$label}\" tidak ditemukan. Pilih dari dropdown, lengkap dengan awalan [Timbangan] atau [Thermometer].");
        }
        if (count($match) > 1) {
            throw new \RuntimeException("Alat \"{$label}\" ganda, tidak bisa dibedakan.");
        }
        $row['alat_uuid'] = $match[0]['uuid'];
        $row['alat_type'] = $match[0]['type'];

        // Titik ukur: harus ada di daftar jenis alatnya
        $titik = trim((string) ($row['titik_ukur'] ?? ''));
        if ($titik === '') {
            throw new \RuntimeException('Titik Ukur wajib diisi.');
        }
        $allowed = self::TITIK_UKUR[$row['alat_type']];
        $titik   = self::matchOption($titik, $allowed);
        if (!in_array($titik, $allowed, true)) {
            $kind = self::KIND_LABELS[$row['alat_type']];
            throw new \RuntimeException("Titik Ukur \"{$titik}\" tidak tersedia untuk {$kind}.");
        }
        $row['titik_ukur'] = $titik;

        // Nilai baca: koma desimal ("12,5") -> titik
        if (isset($row['nilai_baca']) && is_string($row['nilai_baca'])) {
            $row['nilai_baca'] = str_replace(',', '.', trim($row['nilai_baca']));
        }

        // Jam pemeriksaan (opsional): serial Excel / teks -> "H:i"
        if (array_key_exists('check_time', $row)) {
            $time = self::toHi($row['check_time']);
            if ($time === null) {
                throw new \RuntimeException('Jam Pemeriksaan tidak valid.');
            }
            $row['check_time'] = $time;
        }

        if (isset($row['notes'])) {
            $row['notes'] = (string) $row['notes'];
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

        // uuid detail dibuat otomatis oleh event creating di model
        DetailAlatVerification::create([
            'report_alat_verification_uuid' => $report->uuid,
            'alat_type'                     => $row['alat_type'],
            'alat_uuid'                     => $row['alat_uuid'],
            'titik_ukur'                    => $row['titik_ukur'],
            'nilai_baca'                    => $row['nilai_baca'],
            'check_time'                    => $row['check_time'] ?? null,
            'notes'                         => $row['notes'] ?? null,
        ]);
    }
}