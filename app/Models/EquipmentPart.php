<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentPart extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_parts';

    protected $fillable = ['uuid', 'equipment_uuid', 'part_name'];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid', 'uuid');
    }
}