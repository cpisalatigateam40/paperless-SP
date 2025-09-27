<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DrainageStep extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'drainage_steps';

    protected $fillable = [
        'uuid',
        'step_uuid',
        'start_time',
        'end_time',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function step()
    {
        return $this->belongsTo(StepPasteur::class, 'step_uuid', 'uuid');
    }
}