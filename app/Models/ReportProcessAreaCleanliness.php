<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportProcessAreaCleanliness extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_process_area_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'section_name',
        'created_by',
        'known_by',
        'approved_by',
        'is_audit', 'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = ['is_audit' => 'boolean'];

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [
                    'items' => [
                        'followups' => [],
                    ],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details.items' => [
                'condition'    => ['bad' => ['Kotor', 'kotor'], 'ok' => 'Bersih'],
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
            'details.items.followups' => [
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailProcessAreaCleanliness::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}