<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailFragileItem extends Model
{
    use HasFactory;

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

    public function report()
    {
        return $this->belongsTo(ReportFragileItem::class, 'report_fragile_item_uuid', 'uuid');
    }

    public function item()
    {
        return $this->belongsTo(FragileItem::class, 'fragile_item_uuid', 'uuid');
    }
}