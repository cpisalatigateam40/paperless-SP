<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportMtClean extends Model
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }

    protected $table = 'report_mt_cleans';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit', 'source_uuid'
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'is_audit' => 'boolean'
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
                    'photos' => [],
                ],
            ]);

            return $clone;
        });
    }

    public function area()
    {
        return $this->belongsTo(
            Area::class,
            'area_uuid',
            'uuid'
        );
    }

    public function details()
    {
        return $this->hasMany(
            DetailMtClean::class,
            'report_uuid',
            'uuid'
        );
    }
}