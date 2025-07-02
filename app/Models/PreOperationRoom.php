<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PreOperationRoom extends Model
{
    use HasFactory;

    protected $table = 'pre_operation_rooms';
    protected $fillable = ['uuid', 'report_uuid', 'section_uuid', 'condition', 'corrective_action', 'verification'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportPreOperation::class, 'report_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowUpPreOperationRoom::class, 'pre_operation_room_uuid', 'uuid');
    }
}