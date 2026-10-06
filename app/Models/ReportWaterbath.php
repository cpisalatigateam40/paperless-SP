<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportWaterbath extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_waterbaths';
    
    protected $fillable = [
        'uuid', 'area_uuid', 'date', 'shift', 'created_by',
        'known_by', 'approved_by', 'approved_at', 'is_audit',
        'source_uuid',
    ];

    protected $auditEvents = [
        'updated',
    ];

     protected $casts = [
        'is_audit' => 'boolean',
    ];

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details'       => [],
                'pasteurisasi'  => [],
                'coolingShocks' => [],
                'drippings'     => [],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        // suhu air aktual harus sama dengan suhu air setting; hanya jika keduanya terisi
        $actualDiffers = fn ($row) =>
            is_numeric($row->water_temp_setting)
            && is_numeric($row->water_temp_actual)
            && abs((float) $row->water_temp_actual - (float) $row->water_temp_setting) > 0.0001;

        $rule = [
            'water_temp_actual' => [
                'when' => $actualDiffers,
                'ok'   => fn ($row) => $row->water_temp_setting,
            ],
        ];

        return [
            'pasteurisasi'  => $rule,
            'coolingShocks' => $rule,
        ];
    }

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