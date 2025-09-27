<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailFragileItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_fragile_items';

    protected $fillable = [
        'uuid',
        'report_fragile_item_uuid',
        'fragile_item_uuid',
        'actual_quantity',
        'time_start',
        'time_end',
        'notes',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportFragileItem::class, 'report_fragile_item_uuid', 'uuid');
    }

    public function item()
    {
        return $this->belongsTo(FragileItem::class, 'fragile_item_uuid', 'uuid');
    }
}