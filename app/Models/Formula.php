<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use Illuminate\Support\Str;

class Formula extends Model
{
    use HasFactory;

    protected $table = 'formulas';

    protected $fillable = [
        'area_uuid',
        'product_uuid',
        'product_name',
        'formula_name',
        'category'
    ];

    const CATEGORY_PRODUK = 'produk';
    const CATEGORY_SAUS_FLA_KULIT = 'saus_fla_kulit';

    public static function categories(): array
    {
        return [
            self::CATEGORY_PRODUK => 'Formulasi Produk',
            self::CATEGORY_SAUS_FLA_KULIT => 'Formulasi Produk Saus, Fla, Kulit Siomay & Gyoza',
        ];
    }

    // null atau 'produk' dianggap satu kategori yang sama (formulasi produk existing)
    public function scopeCategory($query, $category)
    {
        if (empty($category) || $category === self::CATEGORY_PRODUK) {
            return $query->where(function ($q) {
                $q->whereNull('category')->orWhere('category', self::CATEGORY_PRODUK);
            });
        }

        return $query->where('category', $category);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function formulations()
    {
        return $this->hasMany(Formulation::class, 'formula_uuid', 'uuid');
    }

    public function detailReports()
    {
        return $this->hasMany(DetailProcessProd::class, 'formula_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}