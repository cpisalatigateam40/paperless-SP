<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CookingLoss extends Model
{
    use HasFactory;

    protected $table = 'cooking_losses';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'batch_code',
        'raw_weight',
        'cooked_weight',
        'loss_kg',
        'loss_percent'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailMaurerCooking::class, 'report_detail_uuid', 'uuid');
    }
}