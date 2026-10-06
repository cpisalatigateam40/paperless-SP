<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportStorageRmCleanliness extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_storage_rm_cleanliness';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'room_name',
        'created_by',
        'known_by',
        'approved_by',
        'is_audit',
        'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];

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
        // nilai tidak OK => nilai OK pasangannya (berbeda per item)
        $conditionMap = [
            'Tidak tertata rapi'            => 'Tertata rapi',
            'Penempatan tidak sesuai'       => 'Sesuai tagging dan jenis alergen',
            'Tidak bersih / ada kontaminan' => 'Bersih dan bebas kontaminan',
        ];

        $conditionBad = fn ($row) => isset($conditionMap[$row->condition]);

        return [
            'details.items' => [
                'condition' => [
                    'when' => $conditionBad,
                    'ok'   => fn ($row) => $conditionMap[$row->condition],
                ],
                // catatan hanya diganti untuk item yang kondisinya tidak OK (item 1-3)
                'notes' => [
                    'when' => $conditionBad,
                    'ok'   => json_encode(['Sesuai']),
                ],
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
            'details.items.followups' => [
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
        ];
    }

    public function details()
    {
        return $this->hasMany(DetailStorageRmCleanliness::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}