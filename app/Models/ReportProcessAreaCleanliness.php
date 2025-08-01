<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportProcessAreaCleanliness extends Model
{
    use HasFactory;

    protected $table = 'report_process_area_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'section_name',
        'created_by',
        'known_by',
        'approved_by',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailProcessAreaCleanliness::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}