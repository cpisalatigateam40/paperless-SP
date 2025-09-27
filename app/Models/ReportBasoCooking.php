<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class ReportBasoCooking extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_baso_cookings';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'product_uuid',
        'std_core_temp',
        'std_weight',
        'set_boiling_1',
        'set_boiling_2',
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

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(DetailBasoCooking::class, 'report_uuid', 'uuid');
    }
}