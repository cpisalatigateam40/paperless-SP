<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class FollowupDetailRoomCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'followup_detail_room_cleanliness';

    protected $fillable = [
        'detail_room_uuid',
        'notes',
        'corrective_action',
        'verification'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailRoomCleanliness::class, 'detail_room_uuid', 'uuid');
    }

}