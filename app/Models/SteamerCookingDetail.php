<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class SteamerCookingDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'steamer_cooking_details';

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'batch_uuid',
        'production_code',
        'start_process',
        'end_process',
        'setup_time',
        'room_temp',
        'sensory_bentuk',
        'sensory_warna',
        'sensory_aroma',
        'sensory_rasa',
        'sensory_tekstur',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function batch()
    {
        return $this->belongsTo(SteamerCookingBatch::class, 'batch_uuid', 'uuid');
    }

    public function coreTemps()
    {
        return $this->hasMany(SteamerCookingCoreTemp::class, 'detail_uuid', 'uuid')->orderBy('sequence');
    }
}