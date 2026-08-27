<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailChangeoverCleaning extends Model
{
    use HasFactory;

    protected $table = 'detail_changeover_cleanings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'group',
        'item_uuid',
        'item_name',
        'product_uuid',
        'time',
        'score',
        'explanation',
        'notes',
        'corrective_action',
        'production_code',
    ];

    protected $casts = [
        'score' => 'integer',
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
     * Relasi ke report (header)
     */
    public function report()
    {
        return $this->belongsTo(ReportChangeoverCleaning::class, 'report_uuid', 'uuid');
    }

    /**
     * Relasi ke master item checklist
     */
    public function item()
    {
        return $this->belongsTo(MasterChecklistItem::class, 'item_uuid', 'uuid');
    }

    /**
     * Relasi ke product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->item_name ?? $this->item?->name ?? '-';
    }
}