<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class ReportSiomay extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_siomays';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'production_code',
        'date',
        'shift',
        'start_time',
        'end_time',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
        static::addGlobalScope(new UserAreaScope);
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Produk utama
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    // Relasi ke Detail Proses
    public function details()
    {
        return $this->hasMany(DetailSiomay::class, 'report_uuid', 'uuid');
    }
}