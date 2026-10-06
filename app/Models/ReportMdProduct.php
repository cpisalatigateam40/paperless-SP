<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportMdProduct extends Model implements Auditable
{
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    
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
}