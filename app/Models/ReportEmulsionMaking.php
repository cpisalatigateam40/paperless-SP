<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportEmulsionMaking extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyReportToAudit;
    }
    use \OwenIt\Auditing\Auditable;

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
}