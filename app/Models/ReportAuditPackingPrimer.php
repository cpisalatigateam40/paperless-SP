<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class ReportAuditPackingPrimer extends Model
{
    use HasFactory;

    protected $table = 'report_audit_packing_primers';

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'section_uuid',
        'product_uuid',
        'date',
        'shift',
        'line',
        'production_code',
        'tujuan',
        'karyawan',
        'audit_score',
        'tindakan',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => $model->uuid = Str::uuid());
        static::addGlobalScope(new UserAreaScope);
    }

    // Relasi ke Area (global scope)
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Section ("Area" pada form, mis. Packing)
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    // Relasi ke Produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }

    // Relasi ke Detail item verifikasi (jawaban Yes/No + keterangan per item)
    public function details()
    {
        return $this->hasMany(DetailAuditPackingPrimer::class, 'report_uuid', 'uuid');
    }

    public function foodSafetyDetails()
    {
        return $this->details()->whereHas('item', fn ($q) => $q->where('category', 'food_safety'));
    }

    public function foodQualityDetails()
    {
        return $this->details()->whereHas('item', fn ($q) => $q->where('category', 'food_quality'));
    }

    public function processComplianceDetails()
    {
        return $this->details()->whereHas('item', fn ($q) => $q->where('category', 'process_compliance'));
    }
}