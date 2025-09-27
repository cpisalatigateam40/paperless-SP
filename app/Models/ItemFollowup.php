<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ItemFollowup extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'item_id',
        'notes',
        'action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function item()
    {
        return $this->belongsTo(ItemProcessAreaCleanliness::class, 'item_id');
    }
}