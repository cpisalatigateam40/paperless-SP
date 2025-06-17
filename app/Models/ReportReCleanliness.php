<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportReCleanliness extends Model
{
    use HasFactory;

    protected $table = 'report_re_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function roomDetails(): HasMany
    {
        return $this->hasMany(DetailRoomCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function equipmentDetails(): HasMany
    {
        return $this->hasMany(DetailEquipmentCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}