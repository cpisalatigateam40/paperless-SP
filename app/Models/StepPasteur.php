<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class StepPasteur extends Model
{
    use HasFactory;

    protected $table = 'step_pasteurs';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'step_name',
        'step_order',
        'step_type',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailPasteur::class, 'detail_uuid', 'uuid');
    }

    public function standardStep()
    {
        return $this->hasOne(StandardStep::class, 'step_uuid', 'uuid');
    }

    public function drainageStep()
    {
        return $this->hasOne(DrainageStep::class, 'step_uuid', 'uuid');
    }

    public function finishStep()
    {
        return $this->hasOne(FinishStep::class, 'step_uuid', 'uuid');
    }
}