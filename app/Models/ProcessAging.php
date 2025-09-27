<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ProcessAging extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'process_agings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'aging_process',
        'stuffing_result'
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