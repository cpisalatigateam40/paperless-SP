<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemStorageRmCleanliness extends Model
{
    use HasFactory;

    protected $table = 'item_storage_rm_cleanliness';
    protected $fillable = [
        'detail_uuid',
        'item',
        'condition',
        'notes',
        'corrective_action',
        'verification'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailStorageRmCleanliness::class, 'detail_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowupCleanlinessStorage::class, 'item_storage_rm_cleanliness_id');
    }
}