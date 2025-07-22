<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseStuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stuffer_id',
        'actual_case_1',
        'actual_case_2'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailWeightStuffer::class, 'stuffer_id');
    }
}