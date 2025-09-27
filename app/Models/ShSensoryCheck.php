<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ShSensoryCheck extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

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
        return $this->belongsTo(DetailMaurerCooking::class, 'report_detail_uuid', 'uuid');
    }
}