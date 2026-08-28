<?php
// app/Models/MasterBoilingTankStandard.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterBoilingTankStandard extends Model
{
    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'suhu_tangki_1_min',
        'suhu_tangki_1_max',
        'suhu_tangki_2_min',
        'suhu_tangki_2_max',
        'berat_mentah_min',
        'berat_mentah_max',
        'berat_matang_min',
        'berat_matang_max',
        'actual_core_temp_min',
        'actual_core_temp_max',
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

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    /**
     * Format tampilan standar, otomatis nge-handle yang range vs non-range.
     * Contoh: "75 - 85" kalau ada max, atau "16" doang kalau max kosong.
     */
    public function formatRange(?string $min, ?string $max): ?string
    {
        $minVal = $this->{$min};
        $maxVal = $this->{$max};

        if (is_null($minVal)) {
            return null;
        }

        return is_null($maxVal) ? (string) $minVal : "{$minVal} - {$maxVal}";
    }

    public function getSuhuTangki1LabelAttribute(): ?string
    {
        return $this->formatRange('suhu_tangki_1_min', 'suhu_tangki_1_max');
    }

    public function getSuhuTangki2LabelAttribute(): ?string
    {
        return $this->formatRange('suhu_tangki_2_min', 'suhu_tangki_2_max');
    }

    public function getBeratMentahLabelAttribute(): ?string
    {
        return $this->formatRange('berat_mentah_min', 'berat_mentah_max');
    }

    public function getBeratMatangLabelAttribute(): ?string
    {
        return $this->formatRange('berat_matang_min', 'berat_matang_max');
    }

    public function getActualCoreTempLabelAttribute(): ?string
    {
        return $this->formatRange('actual_core_temp_min', 'actual_core_temp_max');
    }
}