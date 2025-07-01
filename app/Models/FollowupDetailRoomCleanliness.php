<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupDetailRoomCleanliness extends Model
{
    use HasFactory;

    protected $table = 'followup_detail_room_cleanliness';

    protected $fillable = [
        'detail_room_uuid',
        'notes',
        'corrective_action',
        'verification'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailRoomCleanliness::class, 'detail_room_uuid', 'uuid');
    }

}