<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class ReportWaterbath extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_waterbaths';
    
    protected $fillable = [
        'uuid', 'area_uuid', 'date', 'shift', 'created_by',
        'known_by', 'approved_by', 'approved_at'
    ];

    protected $auditEvents = [
        'updated',
    ];

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke detail produk
    public function details()
    {
        return $this->hasMany(DetailWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke pasteurisasi
    public function pasteurisasi()
    {
        return $this->hasMany(PasteurisasiWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke cooling shock
    public function coolingShocks()
    {
        return $this->hasMany(CoolingShockWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke dripping
    public function drippings()
    {
        return $this->hasMany(DrippingWaterbath::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}