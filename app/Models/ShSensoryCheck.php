<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ShSensoryCheck extends Model
{
    use HasFactory;

    protected $table = 'sh_sensory_checks';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'ripeness',
        'aroma',
        'texture',
        'color',
        'taste',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailMaurerCooking::class, 'report_detail_uuid', 'uuid');
    }
}