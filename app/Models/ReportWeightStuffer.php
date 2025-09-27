<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class ReportWeightStuffer extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailWeightStuffer::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}