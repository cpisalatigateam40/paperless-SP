<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class ReportChlorineResidue extends Model
{
    use HasFactory;

    protected $table = 'report_chlorine_residues';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'section_uuid',
        'month',
        'sampling_point',
        'approved_by',
        'approved_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailChlorineResidue::class, 'report_uuid', 'uuid');
    }
}