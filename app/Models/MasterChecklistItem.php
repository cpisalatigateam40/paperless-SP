<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'master_checklist_items';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'category',
        'name',
        'order_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi ke Area (opsional, kalau item berbeda per area)
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}