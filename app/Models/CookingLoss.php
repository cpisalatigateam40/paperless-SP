<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class CookingLoss extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

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

    protected $auditEvents = [
        'updated',
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