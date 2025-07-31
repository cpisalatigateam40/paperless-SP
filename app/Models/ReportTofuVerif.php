<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportTofuVerif extends Model
{
    use HasFactory;

    protected $table = 'report_tofu_verifs';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });
        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function productInfos()
    {
        return $this->hasMany(TofuProductInfo::class, 'report_uuid', 'uuid');
    }

    public function weightVerifs()
    {
        return $this->hasMany(TofuWeightVerif::class, 'report_uuid', 'uuid');
    }

    public function defectVerifs()
    {
        return $this->hasMany(TofuDefectVerif::class, 'report_uuid', 'uuid');
    }
}