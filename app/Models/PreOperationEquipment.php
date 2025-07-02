<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PreOperationEquipment extends Model
{
    use HasFactory;

    protected $table = 'pre_operation_equipments';
    protected $fillable = ['uuid', 'report_uuid', 'equipment_uuid', 'condition', 'corrective_action', 'verification'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportPreOperation::class, 'report_uuid', 'uuid');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowUpPreOperationEquipment::class, 'pre_operation_equipment_uuid', 'uuid');
    }
}