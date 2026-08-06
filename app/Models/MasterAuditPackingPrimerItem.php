<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MasterAuditPackingPrimerItem extends Model
{
    use HasFactory;

    protected $table = 'master_audit_packing_primer_items';

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'category',
        'item_number',
        'item_verifikasi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('item_number');
    }

    public function details()
    {
        return $this->hasMany(DetailAuditPackingPrimer::class, 'item_uuid', 'uuid');
    }
}