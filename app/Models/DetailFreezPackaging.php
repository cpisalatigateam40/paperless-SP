<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailFreezPackaging extends Model
{
    use HasFactory;

    protected $table = 'detail_freez_packagings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'start_time',
        'end_time',
        'production_code',
        'best_before',
        'corrective_action',
        'verif_after',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportFreezPackaging::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function freezing()
    {
        return $this->hasOne(DataFreezing::class, 'detail_uuid', 'uuid');
    }

    public function kartoning()
    {
        return $this->hasOne(DataCartoning::class, 'detail_uuid', 'uuid');
    }
}