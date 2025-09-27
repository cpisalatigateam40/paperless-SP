<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ProcessSensoric extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'process_sensories';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'homogeneous',
        'stiffness',
        'aroma',
        'foreign_object'
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