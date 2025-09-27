<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ProcessTumbling extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'process_tumblings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'tumbling_process'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailProcessProd::class, 'detail_uuid', 'uuid');
    }
}