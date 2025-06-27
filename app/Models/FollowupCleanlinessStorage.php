<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupCleanlinessStorage extends Model
{
    protected $table = 'followup_cleanliness_storage';

    protected $fillable = [
        'item_storage_rm_cleanliness_id',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function item()
    {
        return $this->belongsTo(ItemStorageRmCleanliness::class, 'item_storage_rm_cleanliness_id');
    }
}