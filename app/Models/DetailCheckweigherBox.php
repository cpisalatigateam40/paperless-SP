<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailCheckweigherBox extends Model
{
    use HasFactory;

    protected $table = 'detail_checkweigher_boxes';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time_inspection',
        'production_code',
        'expired_date',
        'program_number',
        'checkweigher_weight_gr',
        'manual_weight_gr',
        'double_item',
        'weight_under',
        'weight_over',
        'corrective_action',
        'verification',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportCheckweigherBox::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}