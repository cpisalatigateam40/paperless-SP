<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class MasterWaterbath extends Model
{
    use HasFactory;

    protected $table = 'master_waterbaths';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'initial_temp_min',
        'initial_temp_max',
    ];

    protected $casts = [
        'initial_temp_min' => 'decimal:2',
        'initial_temp_max' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function parameters()
    {
        return $this->hasMany(MasterWaterbathParameter::class, 'master_waterbath_uuid', 'uuid');
    }

    // Ambil satu parameter: pasteurisasi | cooling | drying
    public function parameter(string $name): ?MasterWaterbathParameter
    {
        return $this->parameters->firstWhere('parameter', $name);
    }

    public function getPasteurisasiAttribute(): ?MasterWaterbathParameter
    {
        return $this->parameter(MasterWaterbathParameter::PASTEURISASI);
    }

    public function getCoolingAttribute(): ?MasterWaterbathParameter
    {
        return $this->parameter(MasterWaterbathParameter::COOLING);
    }

    public function getDryingAttribute(): ?MasterWaterbathParameter
    {
        return $this->parameter(MasterWaterbathParameter::DRYING);
    }

    public function getInitialTempRangeAttribute(): string
    {
        return MasterWaterbathParameter::formatRange($this->initial_temp_min, $this->initial_temp_max);
    }

    // Dipakai untuk warna merah kalau IT di luar range
    public function isInitialTempInRange($value): ?bool
    {
        return MasterWaterbathParameter::inRange($value, $this->initial_temp_min, $this->initial_temp_max);
    }

    public function getInitialTempLabelAttribute(): string
    {
        return $this->initial_temp_range;
    }

    /**
     * Cari master untuk produk. Kalau uuid tidak ketemu, cocokkan lewat nama produk
     * (karena dropdown memakai MIN(uuid) per nama produk).
     */
    public static function findForProduct(string $productUuid): ?self
    {
        $master = static::with('parameters')->where('product_uuid', $productUuid)->first();

        if (!$master) {
            $name = Product::where('uuid', $productUuid)->value('product_name');

            if ($name) {
                $uuids  = Product::where('product_name', $name)->pluck('uuid');
                $master = static::with('parameters')->whereIn('product_uuid', $uuids)->first();
            }
        }

        return $master;
    }

    public function toStandardArray(): array
    {
        return [
            'found'            => true,
            'initial_temp_min' => $this->initial_temp_min,
            'initial_temp_max' => $this->initial_temp_max,
            'parameters'       => $this->parameters->keyBy('parameter')->map(fn ($p) => [
                'set_temp_min'       => $p->set_temp_min,
                'set_temp_max'       => $p->set_temp_max,
                'cold_zone_temp_min' => $p->cold_zone_temp_min,
                'cold_zone_temp_max' => $p->cold_zone_temp_max,
                'set_time'           => $p->set_time,
                'std_ct_min'         => $p->std_ct_min,
                'std_ct_max'         => $p->std_ct_max,
            ]),
        ];
    }
}