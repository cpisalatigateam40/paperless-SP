<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailStorageRmCleanliness extends Model
{
    use HasFactory;

    protected $table = 'detail_storage_rm_cleanliness';
    protected $fillable = ['uuid', 'report_uuid', 'inspection_hour'];

    public function report()
    {
        return $this->belongsTo(ReportStorageRmCleanliness::class, 'report_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(ItemStorageRmCleanliness::class, 'detail_uuid', 'uuid');
    }
}