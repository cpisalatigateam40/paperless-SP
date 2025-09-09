<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DrainageStep extends Model
{
    use HasFactory;

    protected $table = 'drainage_steps';

    protected $fillable = [
        'uuid',
        'step_uuid',
        'start_time',
        'end_time',
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