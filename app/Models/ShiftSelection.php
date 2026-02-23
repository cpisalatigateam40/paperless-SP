<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_number',
        'group',
        'selected_at'
    ];

    protected $casts = [
        'selected_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getShiftLabelAttribute()
    {
        return "Shift {$this->shift_number} - Group {$this->group}";
    }
}