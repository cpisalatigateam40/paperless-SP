<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportSauce extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_sauces';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'formula_uuid',
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
        'documentation_notes',
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
        $okRule = ['bad' => ['Tidak OK'], 'ok' => 'OK'];

        // aktual harus sama dengan target; hanya jika keduanya terisi
        $tempDiffers = fn ($row) =>
            is_numeric($row->target_temperature)
            && is_numeric($row->actual_temperature)
            && abs((float) $row->actual_temperature - (float) $row->target_temperature) > 0.0001;

        // berat aktual bahan harus sama dengan standar di formulasi
        $amountDiffers = fn ($row) =>
            is_numeric(optional($row->formulation)->weight)
            && is_numeric($row->amount)
            && abs((float) $row->amount - (float) $row->formulation->weight) > 0.0001;

        return [
            'details' => [
                'appearance' => $okRule,
                'color'      => $okRule,
                'aroma'      => $okRule,
                'taste'      => $okRule,
                'texture'    => $okRule,
                'actual_temperature' => [
                    'when' => $tempDiffers,
                    'ok'   => fn ($row) => $row->target_temperature,
                ],
                'product_status' => ['bad' => ['Reject'], 'ok' => 'Release'],
            ],
            'details.rawMaterials' => [
                'sensory' => $okRule,
                'amount'  => [
                    'when' => $amountDiffers,
                    'ok'   => fn ($row) => $row->formulation->weight,
                ],
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
        return $this->hasMany(DetailSauce::class, 'report_uuid', 'uuid');
    }

    public function formula()
    {
        return $this->belongsTo(Formula::class, 'formula_uuid', 'uuid');
    }
}