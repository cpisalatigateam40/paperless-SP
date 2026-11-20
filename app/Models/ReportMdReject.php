<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportMdReject extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyReportToAudit;
    }
    use \OwenIt\Auditing\Auditable;

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

    protected $auditEvents = ['updated'];

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
        return [];
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
}