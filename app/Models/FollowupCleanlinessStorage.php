<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FollowupCleanlinessStorage extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'followup_cleanliness_storage';

    protected $fillable = [
        'item_storage_rm_cleanliness_id',
        'notes',
        'corrective_action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function item()
    {
        return $this->belongsTo(ItemStorageRmCleanliness::class, 'item_storage_rm_cleanliness_id');
    }
}