<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemProcessAreaCleanliness extends Model
{
    use HasFactory;

    protected $table = 'item_process_area_cleanliness';

    protected $fillable = [
        'detail_uuid',
        'item',
        'condition',
        'notes',
        'corrective_action',
        'verification',
        'temperature_actual',
        'temperature_display',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailProcessAreaCleanliness::class, 'detail_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(ItemFollowup::class, 'item_id');
    }
}