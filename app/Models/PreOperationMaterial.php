<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PreOperationMaterial extends Model
{
    use HasFactory;

    protected $table = 'pre_operation_materials';
    protected $fillable = ['uuid', 'report_uuid', 'type', 'item', 'condition', 'corrective_action', 'verification'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportPreOperation::class, 'report_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowUpPreOperationMaterial::class, 'pre_operation_material_uuid', 'uuid');
    }
}