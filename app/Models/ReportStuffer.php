<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportStuffer extends Model
{
    use HasFactory;

    protected $table = 'report_stuffers';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailStuffers()
    {
        return $this->hasMany(DetailStuffer::class, 'report_uuid', 'uuid');
    }

    public function cookingLossStuffers()
    {
        return $this->hasMany(CookingLossStuffer::class, 'report_uuid', 'uuid');
    }
}