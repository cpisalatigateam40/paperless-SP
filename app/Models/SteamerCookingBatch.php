<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SteamerCookingBatch extends Model
{
    use HasFactory;

    protected $table = 'steamer_cooking_batches';

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'steamer_number',
        'trolley_count',
        'tray_per_trolley',
        'start_time',
        'end_time',
    ];


    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportSteamerCooking::class, 'report_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(SteamerCookingDetail::class, 'batch_uuid', 'uuid');
    }
}