<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailEquipmentCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_equipment_cleanliness';

    protected $fillable = [
        'uuid',
        'report_re_uuid',
        'equipment_uuid',
        'equipment_part_uuid',
        'condition',
        'notes',
        'corrective_action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportReCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid', 'uuid');
    }

    public function part()
    {
        return $this->belongsTo(EquipmentPart::class, 'equipment_part_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowupDetailEquipmentCleanliness::class, 'detail_equipment_uuid', 'uuid');
    }

}