<?php

namespace App\Models;

use App\Scopes\UserAreaScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormNumber extends Model
{
    use HasUuids;

    protected $fillable = ['area_uuid', 'report_type', 'form_number'];

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);

        static::creating(function ($model) {
            if (empty($model->area_uuid) && auth()->check()) {
                $model->area_uuid = auth()->user()->area_uuid;
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public static function get(string $areaUuid, string $reportType): ?string
    {
        return static::withoutGlobalScope(UserAreaScope::class)
            ->where('area_uuid', $areaUuid)
            ->where('report_type', $reportType)
            ->value('form_number');
    }
}