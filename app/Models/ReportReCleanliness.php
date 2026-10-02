<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportReCleanliness extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_re_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit', 'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = ['is_audit' => 'boolean'];

    protected static function booted()
    {
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
                'roomDetails' => [
                    'followups' => [],
                ],
                'equipmentDetails' => [
                    'followups' => [],
                ],
            ]);

            return $clone;
        });
    }

    public function roomDetails(): HasMany
    {
        return $this->hasMany(DetailRoomCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function equipmentDetails(): HasMany
    {
        return $this->hasMany(DetailEquipmentCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}