<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CookingLossStuffer extends Model
{
    use HasFactory;

    protected $table = 'cooking_loss_stuffers';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'machine_name',
        'percentage'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportStuffer::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}