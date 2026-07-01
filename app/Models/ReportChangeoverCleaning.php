<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class ReportChangeoverCleaning extends Model
{
    use HasFactory;

    protected $table = 'report_changeover_cleanings';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::addGlobalScope(new UserAreaScope);
    }

    /**
     * Relasi ke Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    /**
     * Relasi ke detail (banyak baris cek per item/produk/jam)
     */
    public function details()
    {
        return $this->hasMany(DetailChangeoverCleaning::class, 'report_uuid', 'uuid');
    }
}