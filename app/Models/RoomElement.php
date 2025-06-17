<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomElement extends Model
{
    use HasFactory;

    protected $table = 'room_elements';

    protected $fillable = ['uuid', 'room_uuid', 'element_name'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_uuid', 'uuid');
    }
}