<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DataFreezing extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'data_freezings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'start_product_temp',
        'end_product_temp',
        'iqf_room_temp',
        'iqf_suction_temp',
        'freezing_time_display',
        'freezing_time_actual',
        'standard_temp'
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
        return $this->belongsTo(DetailFreezPackaging::class, 'detail_uuid', 'uuid');
    }
}