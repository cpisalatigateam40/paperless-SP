<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Validation\Rule;

class ReportMdReject extends Model implements ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyReportToAudit;
    }
    use ParsesExcelValues;

    protected $table = 'report_md_rejects';

    public const TEST_PIECES = ['Fe 1.5 mm', 'Non-Fe 2 mm', 'SUS 2.5 mm'];

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'line',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit',
        'source_uuid',
    ];

    protected $casts = [
        'date'        => 'date',
        'approved_at' => 'datetime',
        'is_audit'    => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'verifications' => [
                'result' => ['bad' => ['not_detected'], 'ok' => 'detected'],
            ],
            'details' => [
                'contamination_found' => ['bad' => [true], 'ok' => false],
                'disposition'         => ['bad' => ['hold', 'reject'], 'ok' => 'release'],
            ],
        ];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyReportToAudit();

            foreach (['verifications', 'details'] as $relation) {
                foreach ($this->{$relation}()->get() as $row) {
                    $copy = $row->replicate();
                    $copy->report_uuid = $clone->uuid;
                    $copy->uuid = (string) Str::uuid();
                    $copy->save();
                }
            }

            return $clone;
        });
    }
    

    public function verifications()
    {
        return $this->hasMany(ReportMdRejectVerification::class, 'report_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(ReportMdRejectDetail::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const RESULT_LABELS = ['detected' => 'Terdeteksi', 'not_detected' => 'Tidak Terdeteksi'];

    // Peta "batch upload + (tanggal|shift|line)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $productCache = null;

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

    // Terdeteksi / Tidak Terdeteksi -> detected / not_detected
    private static function parseResult($v, string $label): string
    {
        $s = mb_strtolower(trim((string) $v));

        return match (true) {
            in_array($s, ['terdeteksi', 'detected'], true) => 'detected',
            in_array($s, ['tidak terdeteksi', 'not_detected', 'not detected'], true) => 'not_detected',
            default => throw new \RuntimeException("Nilai \"{$v}\" pada kolom {$label} tidak valid, gunakan Terdeteksi atau Tidak Terdeteksi."),
        };
    }

    public static function getImportMapping(): array
    {
        $columns = [
            // Laporan (kunci kelompok, diulang di setiap baris satu laporan)
            'Tanggal' => 'date',
            'Shift'   => 'shift',
            'Line'    => 'line',
        ];

        // Verifikasi alat: isi di baris pertama tiap laporan
        foreach (self::TEST_PIECES as $i => $piece) {
            $columns["{$piece} - Hasil Verifikasi"] = "ver_{$i}_result";
            $columns["{$piece} - Keterangan"]       = "ver_{$i}_notes";
        }

        return $columns + [
            // Produk reject (satu per baris)
            'Jam'                    => 'time',
            'Nama Produk'            => 'product_uuid',
            'Kode Produksi'          => 'production_code',
            'Jumlah Reject (pcs)'    => 'reject_qty',
            'Hasil Pengecekan Ulang' => 'recheck_result',
            'Kontaminasi Ditemukan'  => 'contamination_found',
            'Dugaan Penyebab'        => 'suspected_causes',
            'Penyebab Lainnya'       => 'suspected_cause_other',
            'Tindakan Korektif'      => 'corrective_action',
            'Disposisi Produk'       => 'disposition',
        ];
    }

    public static function getImportRules(): array
    {
        $rules = [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'  => 'required|date',
            'shift' => 'required|string|max:50',
            'line'  => 'required|string|max:100',

            // produk reject (kelengkapannya dicek di transformImportRow)
            'product_uuid'          => 'nullable',
            'time'                  => 'nullable|date_format:H:i',
            'production_code'       => 'nullable|string|max:100',
            'reject_qty'            => 'nullable|integer|min:0',
            'recheck_result'        => 'nullable|in:detected,not_detected',
            'contamination_found'   => 'nullable|boolean',
            'suspected_causes'      => 'array',
            'suspected_cause_other' => 'nullable|string|max:255',
            'corrective_action'     => 'nullable|string',
            'disposition'           => ['nullable', Rule::in(array_keys(ReportMdRejectDetail::DISPOSITIONS))],
        ];

        // verifikasi alat (kelengkapannya dicek di importRow)
        foreach (array_keys(self::TEST_PIECES) as $i) {
            $rules["ver_{$i}_result"] = 'nullable|in:detected,not_detected';
            $rules["ver_{$i}_notes"]  = 'nullable|string|max:255';
        }

        return $rules;
    }

    public static function getMasterDataFields(): array
    {
        $result = ['options' => array_values(self::RESULT_LABELS)];

        $fields = [];
        foreach (self::TEST_PIECES as $i => $piece) {
            $fields["ver_{$i}_result"] = ['label' => "{$piece} - Hasil"] + $result;
        }

        return $fields + [
            'product_uuid' => [
                'label'    => 'Nama Produk',
                'callback' => fn () => array_column(self::productOptions(), 'name'),
            ],
            'recheck_result'      => ['label' => 'Hasil Pengecekan Ulang'] + $result,
            'contamination_found' => ['label' => 'Kontaminasi Ditemukan', 'options' => ['Ada', 'Tidak']],
            'disposition'         => ['label' => 'Disposisi Produk', 'options' => array_values(ReportMdRejectDetail::DISPOSITIONS)],
            // Referensi saja (tanpa dropdown): nilai untuk kolom Dugaan Penyebab, dipisah titik koma
            'ref_causes'          => ['label' => 'Referensi Dugaan Penyebab', 'options' => array_values(ReportMdRejectDetail::CAUSES)],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        $row = [
            'date'  => now()->toDateString(),
            'shift' => self::defaultShift(),
            'line'  => 'Line 1',
        ];

        foreach (array_keys(self::TEST_PIECES) as $i) {
            $row["ver_{$i}_result"] = self::RESULT_LABELS['detected'];
        }

        return [$row + [
            'time'                => '08:30',
            'product_uuid'        => self::productOptions()[0]['name'] ?? null,
            'production_code'     => 'CONTOH-001',
            'reject_qty'          => 3,
            'recheck_result'      => self::RESULT_LABELS['not_detected'],
            'contamination_found' => 'Tidak',
            'suspected_causes'    => array_values(ReportMdRejectDetail::CAUSES)[0],
            'corrective_action'   => 'Contoh tindakan korektif, timpa atau hapus baris ini',
            'disposition'         => ReportMdRejectDetail::DISPOSITIONS['release'],
        ]];
    }

    public static function transformImportRow(array $row): array
    {
        // Tanggal
        if (array_key_exists('date', $row)) {
            $date = self::toDate($row['date']);
            if ($date === null) {
                throw new \RuntimeException('Tanggal tidak valid (contoh: 27/10/2026).');
            }
            $row['date'] = $date;
        }

        // Shift: kosong -> session (QC Inspector) atau NON-SHIFT
        $shift = isset($row['shift']) ? trim((string) $row['shift']) : '';
        $row['shift'] = $shift !== '' ? $shift : self::defaultShift();

        // Line
        $line = trim((string) ($row['line'] ?? ''));
        if ($line === '') {
            throw new \RuntimeException('Line wajib diisi.');
        }
        $row['line'] = $line;

        // Verifikasi alat (hanya di baris pertama tiap laporan)
        foreach (self::TEST_PIECES as $i => $piece) {
            if (isset($row["ver_{$i}_result"])) {
                $row["ver_{$i}_result"] = self::parseResult($row["ver_{$i}_result"], "{$piece} - Hasil Verifikasi");
            }
            if (isset($row["ver_{$i}_notes"])) {
                $row["ver_{$i}_notes"] = (string) $row["ver_{$i}_notes"];
            }
        }

        // Produk reject: opsional, baris tanpa produk hanya membawa laporan dan verifikasi
        $hasDetail = false;
        foreach (['product_uuid', 'time', 'production_code', 'reject_qty', 'recheck_result',
                  'contamination_found', 'suspected_causes', 'suspected_cause_other',
                  'corrective_action', 'disposition'] as $key) {
            if (isset($row[$key])) {
                $hasDetail = true;
                break;
            }
        }
        $row['has_detail'] = $hasDetail;

        if (!$hasDetail) {
            return $row;
        }

        // Sama dengan atribut required di form create
        $required = [
            'product_uuid'        => 'Nama Produk',
            'time'                => 'Jam',
            'production_code'     => 'Kode Produksi',
            'reject_qty'          => 'Jumlah Reject',
            'recheck_result'      => 'Hasil Pengecekan Ulang',
            'contamination_found' => 'Kontaminasi Ditemukan',
            'disposition'         => 'Disposisi Produk',
        ];
        foreach ($required as $key => $label) {
            if (!isset($row[$key])) {
                throw new \RuntimeException("{$label} wajib diisi bila data reject produk diisi.");
            }
        }

        // Produk: nama -> uuid
        $name  = trim((string) $row['product_uuid']);
        $match = array_values(array_filter(
            self::productOptions(),
            fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($name)
        ));
        if (count($match) === 0) {
            throw new \RuntimeException("Produk \"{$name}\" tidak ditemukan.");
        }
        if (count($match) > 1) {
            throw new \RuntimeException("Produk \"{$name}\" ganda, tidak bisa dibedakan.");
        }
        $row['product_uuid'] = $match[0]['uuid'];

        // Jam
        $time = self::toHi($row['time']);
        if ($time === null) {
            throw new \RuntimeException('Jam tidak valid.');
        }
        $row['time'] = $time;

        // Jumlah reject: angka bulat
        $qty = $row['reject_qty'];
        if (is_string($qty)) {
            $qty = str_replace(',', '.', trim($qty));
        }
        if (is_numeric($qty) && floor((float) $qty) == (float) $qty) {
            $qty = (int) $qty;
        }
        $row['reject_qty'] = $qty;

        // Hasil pengecekan ulang
        $row['recheck_result'] = self::parseResult($row['recheck_result'], 'Hasil Pengecekan Ulang');

        // Kontaminasi: Ada / Tidak
        $v = mb_strtolower(trim((string) $row['contamination_found']));
        $row['contamination_found'] = match (true) {
            in_array($v, ['ada', 'ya', 'yes', '1', 'true'], true) => true,
            in_array($v, ['tidak', 'no', '0', 'false'], true)     => false,
            default => throw new \RuntimeException('Kontaminasi Ditemukan harus Ada atau Tidak.'),
        };

        // Disposisi: label atau key -> key
        $v     = mb_strtolower(trim((string) $row['disposition']));
        $found = null;
        foreach (ReportMdRejectDetail::DISPOSITIONS as $key => $label) {
            if ($v === mb_strtolower($label) || $v === $key) {
                $found = $key;
                break;
            }
        }
        if ($found === null) {
            throw new \RuntimeException("Disposisi \"{$row['disposition']}\" tidak dikenal.");
        }
        $row['disposition'] = $found;

        // Dugaan penyebab: daftar dipisah titik koma -> array key
        $causes = [];
        foreach (self::splitList($row['suspected_causes'] ?? '') as $item) {
            $s   = mb_strtolower($item);
            $key = null;
            foreach (ReportMdRejectDetail::CAUSES as $k => $label) {
                if ($s === mb_strtolower($label) || $s === $k) {
                    $key = $k;
                    break;
                }
            }
            if ($key === null) {
                throw new \RuntimeException("Dugaan penyebab \"{$item}\" tidak dikenal.");
            }
            $causes[] = $key;
        }

        // Penyebab lainnya terisi -> pilihan "Lainnya" otomatis ikut dipilih
        if (isset($row['suspected_cause_other'])) {
            $row['suspected_cause_other'] = (string) $row['suspected_cause_other'];
            $causes[] = 'other';
        }
        $row['suspected_causes'] = array_values(array_unique($causes));

        if (isset($row['corrective_action'])) {
            $row['corrective_action'] = (string) $row['corrective_action'];
        }
        if (isset($row['production_code'])) {
            $row['production_code'] = (string) $row['production_code'];
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift + line dalam SATU upload
        $key  = implode('|', [$row['date'], $row['shift'], $row['line']]);
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'line'       => $row['line'],
                'created_by' => $row['created_by'],
            ]
        );

        $hasVerification = false;
        foreach (array_keys(self::TEST_PIECES) as $i) {
            if (isset($row["ver_{$i}_result"]) || isset($row["ver_{$i}_notes"])) {
                $hasVerification = true;
            }
        }

        if ($report->wasRecentlyCreated) {
            // Baris pertama kelompok: verifikasi ketiga test piece wajib
            foreach (self::TEST_PIECES as $i => $piece) {
                if (!isset($row["ver_{$i}_result"])) {
                    throw new \RuntimeException(
                        "Baris pertama tiap kelompok (Tanggal, Shift, Line) wajib mengisi Hasil Verifikasi untuk {$piece}."
                    );
                }
            }

            foreach (self::TEST_PIECES as $i => $piece) {
                $report->verifications()->create([
                    'test_piece' => $piece,
                    'result'     => $row["ver_{$i}_result"],
                    'notes'      => $row["ver_{$i}_notes"] ?? null,
                ]);
            }
        } elseif ($hasVerification) {
            throw new \RuntimeException(
                'Verifikasi alat sudah diisi di baris pertama kelompok ini. Kosongkan kolom verifikasi pada baris ini.'
            );
        }

        if (!$row['has_detail']) {
            return; // laporan tanpa produk reject
        }

        $report->details()->create([
            'product_uuid'          => $row['product_uuid'],
            'time'                  => $row['time'],
            'production_code'       => $row['production_code'],
            'reject_qty'            => $row['reject_qty'],
            'recheck_result'        => $row['recheck_result'],
            'contamination_found'   => $row['contamination_found'],
            'suspected_causes'      => $row['suspected_causes'],
            'suspected_cause_other' => $row['suspected_cause_other'] ?? null,
            'corrective_action'     => $row['corrective_action'] ?? null,
            'disposition'           => $row['disposition'],
        ]);
    }
}