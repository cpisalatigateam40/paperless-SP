<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportSiomay extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

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
}