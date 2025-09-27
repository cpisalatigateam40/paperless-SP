<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class FsProcessStep extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'fs_process_steps';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'step_name',
        'time_minutes_1',
        'time_minutes_2',
        'room_temp_1',
        'room_temp_2',
        'air_circulation_1',
        'air_circulation_2',
        'product_temp_1',
        'product_temp_2',
        'actual_product_temp',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailFessmanCooking::class, 'report_detail_uuid', 'uuid');
    }
}