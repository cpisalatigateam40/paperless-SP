<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportWeightStuffer extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

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
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [
                    'townsend'       => [],
                    'hitech'         => [],
                    'vemag'          => [],
                    'vemag2'         => [],
                    'handtmann'      => [],
                    'cases'          => [],
                    'weights'        => [],
                    'documentations' => [],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'weight_status' => ['bad' => ['NOT OK'], 'ok' => 'OK'],
                'long_status'   => ['bad' => ['NOT OK'], 'ok' => 'OK'],
                'fla_status'    => ['bad' => ['NOT OK'], 'ok' => 'OK'],
            ],
        ];
    }

    protected $auditEvents = [
        'updated',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailWeightStuffer::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}