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

class ReportTofuVerif extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_tofu_verifs';

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
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
                'productInfos' => [],
                'weightVerifs' => [],
                'defectVerifs' => [],
            ]);

            return $clone;
        });
    }

    protected $auditEvents = [
        'updated',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function productInfos()
    {
        return $this->hasMany(TofuProductInfo::class, 'report_uuid', 'uuid');
    }

    public function weightVerifs()
    {
        return $this->hasMany(TofuWeightVerif::class, 'report_uuid', 'uuid');
    }

    public function defectVerifs()
    {
        return $this->hasMany(TofuDefectVerif::class, 'report_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    // Key = nilai yang tersimpan (sama dengan form create)
    public const WEIGHT_CATEGORIES = [
        'under'    => 'Under (< 11gr)',
        'standard' => 'Standard (11 - 13gr)',
        'over'     => 'Over (> 13gr)',
    ];
    public const DEFECT_TYPES = [
        'hole'      => 'Berlubang',
        'stain'     => 'Noda',
        'asymmetry' => 'Tidak Bulat Simetris',
        'other'     => 'Lain-lain',
        'good'      => 'Produk Bagus',
        'note'      => 'Keterangan',
    ];

    // Peta "batch upload + (tanggal|shift)" => uuid laporan
    private static array $importReportUuids = [];

    // key internal => ['category', 'label'], mis. 'w_under_turus' => 'Under (< 11gr) - Turus'
    private static function metricColumns(): array
    {
        $metrics = ['turus' => 'Turus', 'total' => 'Jumlah', 'percentage' => '%'];
        $groups  = ['w' => self::WEIGHT_CATEGORIES, 'd' => self::DEFECT_TYPES];

        $columns = [];
        foreach ($groups as $prefix => $items) {
            foreach ($items as $cat => $catLabel) {
                foreach ($metrics as $metric => $metricLabel) {
                    $columns["{$prefix}_{$cat}_{$metric}"] = [
                        'category' => $cat,
                        'label'    => "{$catLabel} - {$metricLabel}",
                    ];
                }
            }
        }
        return $columns;
    }

    // Sama dengan calculateExpirationDate() di JavaScript form create (24 bulan dari kode batch)
    private static function bestBeforeFromCode(string $code, int $months = 24): ?string
    {
        if (!preg_match('/([A-Z]{2}\d{2})/i', $code, $m)) {
            return null;
        }

        $batch = strtoupper($m[1]);
        $year  = 2009 + (ord($batch[0]) - ord('A'));
        $month = ord($batch[1]) - ord('A') + 1;
        $day   = (int) substr($batch, 2, 2);

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        return \Carbon\Carbon::create($year, $month, $day)
            ->addMonthsNoOverflow($months)
            ->toDateString();
    }

    public static function getImportMapping(): array
    {
        $columns = [
            // Laporan
            'Tanggal'             => 'date',
            'Shift'               => 'shift',
            // Info produk (satu baris = satu produk)
            'Kode Produksi'       => 'production_code',
            'Best Before'         => 'expired_date',
            'Jumlah Sampel (pcs)' => 'sample_amount',
        ];

        // Hasil pemeriksaan berat dan defect: turus, jumlah, persen per kategori
        foreach (self::metricColumns() as $key => $meta) {
            $columns[$meta['label']] = $key;
        }

        return $columns;
    }

    public static function getImportRules(): array
    {
        $rules = [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan + info produk (dari Excel)
            'date'            => 'required|date',
            'shift'           => 'required|string|max:50',
            'production_code' => 'required|string|max:100',
            'expired_date'    => 'nullable|date',
            'sample_amount'   => 'nullable|numeric',
        ];

        foreach (array_keys(self::metricColumns()) as $key) {
            $rules[$key] = 'nullable|numeric';
        }

        return $rules;
    }

    // Tidak ada dropdown: semua kolom berupa angka atau teks bebas
    public static function getMasterDataFields(): array
    {
        return [];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        $row = [
            'date'            => now()->toDateString(),
            'shift'           => self::defaultShift(),
            'production_code' => 'CONTOH-001',
            'expired_date'    => now()->addYear()->toDateString(),
            'sample_amount'   => 100,
        ];

        $sample = [
            'under' => 5, 'standard' => 90, 'over' => 5,
            'hole' => 2, 'stain' => 1, 'asymmetry' => 1, 'other' => 0, 'good' => 96, 'note' => 0,
        ];
        foreach (self::metricColumns() as $key => $meta) {
            $row[$key] = $sample[$meta['category']];
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

        // Kode produksi bisa terbaca sebagai angka oleh Excel
        if (isset($row['production_code'])) {
            $row['production_code'] = trim((string) $row['production_code']);
        }

        // Best before: dari kolom; bila kosong dihitung dari kode produksi (sama dengan form)
        if (array_key_exists('expired_date', $row)) {
            $expired = self::toDate($row['expired_date']);
            if ($expired === null) {
                throw new \RuntimeException('Best Before tidak valid (contoh: 2027-10-27).');
            }
            $row['expired_date'] = $expired;
        } elseif (!empty($row['production_code'])) {
            $row['expired_date'] = self::bestBeforeFromCode($row['production_code']);
        }

        // Angka bertulis koma desimal ("5,5") -> titik
        foreach (array_merge(['sample_amount'], array_keys(self::metricColumns())) as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Satu baris Excel = satu laporan. Verifikasi berat/defect hanya terhubung ke laporan
        // (tabelnya tidak punya product_info_uuid), jadi satu laporan hanya boleh satu produk.
        $report = static::create([
            'area_uuid'  => $row['area_uuid'],
            'date'       => $row['date'],
            'shift'      => $row['shift'],
            'created_by' => $row['created_by'],
        ]);

        // Info produk (uuid dibuat otomatis oleh event creating di model)
        TofuProductInfo::create([
            'report_uuid'     => $report->uuid,
            'production_code' => $row['production_code'],
            'expired_date'    => $row['expired_date'] ?? null,
            'sample_amount'   => $row['sample_amount'] ?? null,
        ]);

        // Verifikasi berat dan defect: semua kategori selalu dibuat, sama dengan store()
        $groups = [
            'w' => [TofuWeightVerif::class, 'weight_category', self::WEIGHT_CATEGORIES],
            'd' => [TofuDefectVerif::class, 'defect_type', self::DEFECT_TYPES],
        ];

        foreach ($groups as $prefix => [$modelClass, $column, $items]) {
            foreach (array_keys($items) as $cat) {
                $modelClass::create([
                    'report_uuid' => $report->uuid,
                    $column       => $cat,
                    'turus'       => $row["{$prefix}_{$cat}_turus"] ?? null,
                    'total'       => $row["{$prefix}_{$cat}_total"] ?? null,
                    'percentage'  => $row["{$prefix}_{$cat}_percentage"] ?? null,
                ]);
            }
        }
    }
}