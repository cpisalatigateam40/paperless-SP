<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailFragileItemManual extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_fragile_item_manuals';

    protected $fillable = [
        'uuid',
        'report_fragile_item_uuid',
        'section_uuid',
        'sub_area',
        'item_name',
        'quantity',
        'condition',
        'employee_name',
        'issue_notes',
        'corrective_action',
    ];

    protected $auditEvents = ['updated'];

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

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }
}