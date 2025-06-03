<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportStorageRmCleanliness extends Model
{
    use HasFactory;

    protected $table = 'report_storage_rm_cleanliness';
    protected $fillable = [
        'uuid',
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
}