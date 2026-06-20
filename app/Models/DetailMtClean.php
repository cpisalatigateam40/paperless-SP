<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailMtClean extends Model
{
    use HasFactory;

    protected $table = 'detail_mt_cleans';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'mt_1',
        'mt_2',
        'finding_type',
        'condition',
        'note',
        'corrective_action',
    ];

    protected $casts = [
       
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

    public function report()
    {
        return $this->belongsTo(
            ReportMtClean::class,
            'report_uuid',
            'uuid'
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_uuid',
            'uuid'
        );
    }
}