<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailAuditPackingPrimer extends Model
{
    use HasFactory;

    protected $table = 'detail_audit_packing_primers';

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'item_uuid',
        'verifikasi',
        'keterangan',
    ];

    

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportAuditPackingPrimer::class, 'report_uuid', 'uuid');
    }

    public function item()
    {
        return $this->belongsTo(MasterAuditPackingPrimerItem::class, 'item_uuid', 'uuid');
    }
}