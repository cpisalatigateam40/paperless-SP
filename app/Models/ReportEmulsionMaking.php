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
        // Peta "batch upload + (tanggal|shift|jenis|kode)" => uuid laporan & header
    private static array $importReportUuids = [];

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

    public const EMULSION_TYPES = ['Emulsi Oil', 'Emulsi Skin', 'Emulsi Gel', 'Emulsi GMB'];

    public static function getImportMapping(): array
    {
        return [
            // Laporan + header (sama di semua baris satu laporan)
            'Tanggal'                      => 'date',
            'Shift'                        => 'shift',
            'Jenis Emulsi'                 => 'emulsion_type',
            'Kode Produksi'                => 'production_code',
            // Bahan baku
            'Nama Bahan'                   => 'material_uuid',
            'Berat (kg)'                   => 'weight',
            'Suhu (°C)'                    => 'temperature',
            'Kesesuaian Formula'           => 'conformity',
            // Aging (isi di baris pertama tiap laporan)
            'Waktu Awal Pembuatan Emulsi'  => 'start_aging',
            'Waktu Akhir Pembuatan Emulsi' => 'finish_aging',
            'Sensori Warna'                => 'sensory_color',
            'Sensori Texture'              => 'sensory_texture',
            'Suhu Emulsi After Proses'     => 'temp_after',
            'Hasil Emulsi (Sensori)'       => 'emulsion_result',
        ];
    }

    public static function getImportRules(): array
    {
        $aging = 'required_with:start_aging';

        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan + header (dari Excel)
            'date'            => 'required|date',
            'shift'           => 'required|string|max:50',
            'emulsion_type'   => ['required', Rule::in(self::EMULSION_TYPES)],
            'production_code' => 'nullable|string|max:100',

            // bahan baku (dari Excel)
            'material_uuid' => 'required',
            'material_type' => 'required|in:raw,premix',
            'weight'        => 'required|numeric|min:0',
            'temperature'   => 'nullable|numeric',
            'conformity'    => 'required|in:✓,x',

            // aging (opsional; bila salah satu diisi, semua wajib kecuali suhu after proses)
            'start_aging'     => 'nullable|date_format:H:i|required_with:finish_aging,sensory_color,sensory_texture,temp_after,emulsion_result',
            'finish_aging'    => "nullable|date_format:H:i|{$aging}",
            'sensory_color'   => "nullable|in:✓,x|{$aging}",
            'sensory_texture' => "nullable|in:✓,x|{$aging}",
            'temp_after'      => 'nullable|numeric',
            'emulsion_result' => "nullable|in:OK,Tidak OK|{$aging}",
        ];
    }

    public static function getMasterDataFields(): array
    {
        $check = ['options' => ['✓', 'x']];

        return [
            'emulsion_type' => ['label' => 'Jenis Emulsi', 'options' => self::EMULSION_TYPES],
            'material_uuid' => [
                'label'    => 'Bahan Baku',
                'callback' => fn () => array_column(self::materialOptions(), 'label'),
            ],
            'conformity'      => ['label' => 'Kesesuaian Formula'] + $check,
            'sensory_color'   => ['label' => 'Sensori Warna'] + $check,
            'sensory_texture' => ['label' => 'Sensori Texture'] + $check,
            'emulsion_result' => ['label' => 'Hasil Emulsi', 'options' => ['OK', 'Tidak OK']],
        ];
    }

    // Baris contoh di template, terisi penuh (termasuk aging)
    public static function getImportPrefillRows(): array
    {
        return [[
            'date'            => now()->toDateString(),
            'shift'           => self::defaultShift(),
            'emulsion_type'   => self::EMULSION_TYPES[0],
            'production_code' => 'EMULSI-001',
            'material_uuid'   => self::materialOptions()[0]['label'] ?? null,
            'weight'          => 10.5,
            'temperature'     => 25.0,
            'conformity'      => '✓',
            'start_aging'     => '08:00',
            'finish_aging'    => '08:30',
            'sensory_color'   => '✓',
            'sensory_texture' => '✓',
            'temp_after'      => 28.5,
            'emulsion_result' => 'OK',
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

        // Jenis emulsi: samakan huruf besar/kecil dengan daftar resmi
        if (isset($row['emulsion_type'])) {
            $v = mb_strtolower(trim((string) $row['emulsion_type']));
            foreach (self::EMULSION_TYPES as $type) {
                if (mb_strtolower($type) === $v) {
                    $row['emulsion_type'] = $type;
                    break;
                }
            }
        }

        // Kode produksi bisa terbaca sebagai angka oleh Excel
        if (isset($row['production_code'])) {
            $row['production_code'] = trim((string) $row['production_code']);
        }

        // Bahan: label -> uuid + type
        if (isset($row['material_uuid'])) {
            $m = self::resolveMaterial((string) $row['material_uuid']);
            $row['material_uuid'] = $m['uuid'];
            $row['material_type'] = $m['type'];
        }

        // Angka bertulis koma desimal ("10,5") -> titik
        foreach (['weight', 'temperature', 'temp_after'] as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        // Waktu aging: serial Excel / teks -> "H:i"
        foreach (['start_aging', 'finish_aging'] as $key) {
            if (array_key_exists($key, $row)) {
                $time = self::toHi($row[$key]);
                if ($time === null) {
                    throw new \RuntimeException('Waktu aging tidak valid.');
                }
                $row[$key] = $time;
            }
        }

        // Centang: samakan ke ✓ / x
        foreach (['conformity', 'sensory_color', 'sensory_texture'] as $key) {
            if (isset($row[$key])) {
                $v = mb_strtolower(trim((string) $row[$key]));
                $row[$key] = match (true) {
                    in_array($v, ['✓', '√', '✔', 'v', 'ok'], true) => '✓',
                    in_array($v, ['x', '×', 'nok'], true)          => 'x',
                    default                                        => $v,
                };
            }
        }

        // Hasil emulsi: OK / Tidak OK
        if (isset($row['emulsion_result'])) {
            $v = mb_strtolower(trim((string) $row['emulsion_result']));
            $row['emulsion_result'] = match (true) {
                $v === 'ok'                                  => 'OK',
                in_array($v, ['tidak ok', 'nok', 'x'], true) => 'Tidak OK',
                default                                      => trim((string) $row['emulsion_result']),
            };
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Satu laporan + header per kombinasi tanggal, shift, jenis emulsi, kode produksi dalam SATU upload
        $key = implode('|', [
            $row['date'], $row['shift'], $row['emulsion_type'], $row['production_code'] ?? '',
        ]);

        $ids = self::$importReportUuids[$row['import_batch']][$key]
            ??= ['report' => (string) Str::uuid(), 'header' => (string) Str::uuid()];

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $ids['report']],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'created_by' => $row['created_by'],
            ]
        );

        $header = HeaderEmulsionMaking::firstOrCreate(
            ['uuid' => $ids['header']],
            [
                'report_uuid'     => $report->uuid,
                'emulsion_type'   => $row['emulsion_type'],
                'production_code' => $row['production_code'] ?? null,
            ]
        );

        // Aging: satu per laporan. Baris lain dalam laporan yang sama tidak boleh mengisinya lagi.
        if (filled($row['start_aging'] ?? null)) {
            if (AgingEmulsionMaking::where('header_uuid', $header->uuid)->exists()) {
                throw new \RuntimeException(
                    'Aging hanya satu per laporan dan sudah diisi di baris sebelumnya. Kosongkan kolom aging pada baris ini.'
                );
            }

            AgingEmulsionMaking::create([
                'uuid'            => (string) Str::uuid(),
                'header_uuid'     => $header->uuid,
                'start_aging'     => $row['start_aging'],
                'finish_aging'    => $row['finish_aging'],
                'sensory_color'   => $row['sensory_color'],
                'sensory_texture' => $row['sensory_texture'],
                'temp_after'      => $row['temp_after'] ?? null,
                'emulsion_result' => $row['emulsion_result'],
            ]);
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