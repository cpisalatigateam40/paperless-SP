<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailStartupLabel extends Model
{
    use HasFactory;

    protected $table = 'detail_startup_labels';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'best_before',
        'result',
        'corrective_action',
        'packaging'
    ];

    protected $casts = [
        'best_before' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi ke report (header)
     */
    public function report()
    {
        return $this->belongsTo(ReportStartupLabel::class, 'report_uuid', 'uuid');
    }

    /**
     * Relasi ke product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}