<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'notes',
        'action',
        'verification',
    ];

    public function item()
    {
        return $this->belongsTo(ItemProcessAreaCleanliness::class, 'item_id');
    }
}