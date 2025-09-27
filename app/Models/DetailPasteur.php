<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailPasteur extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_pasteurs';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'program_number',
        'product_code',
        'for_packaging_gr',
        'trolley_count',
        'product_temp',
        'qc_paraf',
        'production_paraf',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportPasteur::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function steps()
    {
        return $this->hasMany(StepPasteur::class, 'detail_uuid', 'uuid');
    }
}