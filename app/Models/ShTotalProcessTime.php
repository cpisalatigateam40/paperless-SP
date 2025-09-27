<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ShTotalProcessTime extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sh_total_process_times';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'start_time',
        'end_time',
        'total_duration',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailMaurerCooking::class, 'report_detail_uuid', 'uuid');
    }
}