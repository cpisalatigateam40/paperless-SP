<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class ItemStorageRmCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'item_storage_rm_cleanliness';
    protected $fillable = [
        'detail_uuid',
        'item',
        'condition',
        'notes',
        'corrective_action',
        'verification'
    ];

    protected $auditEvents = [
        'updated',
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