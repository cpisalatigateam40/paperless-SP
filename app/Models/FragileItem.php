<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Scopes\UserAreaScope;
use Illuminate\Support\Str;

class FragileItem extends Model
{
    use HasFactory;
    protected $table = 'fragile_items';

    protected $fillable = [
        'area_uuid',
        'item_name',
        'section_name',
        'owner',
        'quantity',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailReports()
    {
        return $this->hasMany(DetailFragileItem::class, 'fragile_item_uuid', 'uuid');
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