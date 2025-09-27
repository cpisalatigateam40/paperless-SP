<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class FsSensoryCheck extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'fs_sensory_checks';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'ripeness',
        'aroma',
        'taste',
        'texture',
        'color',
        'can_be_twisted',
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