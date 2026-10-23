<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DetailForeignObject extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_foreign_objects';

    public const ACTION_OPTIONS = [
        'reject_musnahkan' => 'Reject / Musnahkan',
        'return'           => 'Return',
        'sortir'           => 'Sortir',
        'rework'           => 'Rework',
        'repack'           => 'Repack',
        'lainnya'          => 'Lainnya',
    ];

    // Opsi dropdown Product Status
    public const PRODUCT_STATUS_OPTIONS = [
        'hold'    => 'Hold',
        'pending' => 'Pending',
        'lainnya' => 'Lainnya',
    ];

    protected function investigation(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::listFromStorage($value),
            set: fn ($value) => self::listForStorage($value),
        );
    }

    // Selalu array. Data lama (satu path teks) dibaca sebagai 1 item.
    protected function evidence(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::listFromStorage($value),
            set: fn ($value) => self::listForStorage($value),
        );
    }

    private static function listFromStorage($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [(string) $value];
    }

    private static function listForStorage($value): ?string
    {
        // Nilai mentah JSON (mis. dari replicate()/copyToAudit) jangan di-encode dua kali
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [$value];
        }

        $items = array_values(array_filter((array) $value, fn ($v) => filled($v)));

        return $items ? json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    }

    public const MAX_EVIDENCE = 6;


    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'gramase',
        'process_stage',
        'line',
        'contaminant_type',          // legacy (teks bebas, data lama)
        'contamination_type_uuid',
        'size_quantity',
        'description',
        'product_status',
        'affected_quantity',
        'disposition',
        'investigation',
        'immediate_correction',
        'corrective_action',
        'evidence',
        'analysis_stage',
        'contaminant_origin',
        'notes',
        'qc_paraf',
        'production_paraf',
        'engineering_paraf',
        'item_type',
        'item_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];


    public function report()
    {
        return $this->belongsTo(ReportForeignObject::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function contaminationType()
    {
        return $this->belongsTo(ContaminationType::class, 'contamination_type_uuid', 'uuid')
            ->withTrashed();
    }

    public const ITEM_TYPES = [
        'product'      => 'Produk',
        'raw_material' => 'Raw Material',
        'premix'       => 'Premix',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'item_uuid', 'uuid');
    }

    public function premix()
    {
        return $this->belongsTo(Premix::class, 'item_uuid', 'uuid');
    }

    // Nama item sesuai tipenya; data lama (item_type null/product) jatuh ke produk
    public function getItemNameAttribute(): string
    {
        return match ($this->item_type) {
            'raw_material' => $this->rawMaterial->material_name ?? '-',
            'premix'       => $this->premix->name ?? '-',
            default        => $this->product->product_name ?? '-',
        };
    }

    public function getItemTypeLabelAttribute(): string
    {
        return self::ITEM_TYPES[$this->item_type ?? 'product'] ?? 'Produk';
    }

}