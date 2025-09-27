<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;


class DetailRoomCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_room_cleanliness';

    protected $fillable = [
        'uuid',
        'report_re_uuid',
        'room_uuid',
        'room_element_uuid',
        'condition',
        'notes',
        'corrective_action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportReCleanliness::class, 'report_re_uuid', 'uuid');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_uuid', 'uuid');
    }

    public function element()
    {
        return $this->belongsTo(RoomElement::class, 'room_element_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowupDetailRoomCleanliness::class, 'detail_room_uuid', 'uuid');
    }
}