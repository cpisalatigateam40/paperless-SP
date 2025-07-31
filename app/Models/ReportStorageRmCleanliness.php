<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportStorageRmCleanliness extends Model
{
    use HasFactory;

    protected $table = 'report_storage_rm_cleanliness';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'room_name',
        'created_by',
        'known_by',
        'approved_by'
    ];

    public function details()
    {
        return $this->hasMany(DetailStorageRmCleanliness::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}