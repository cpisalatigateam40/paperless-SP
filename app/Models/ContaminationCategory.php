<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ContaminationCategory extends Model
{
    use SoftDeletes;

    protected $table = 'contamination_categories';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['uuid', 'name', 'sort_order'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function types()
    {
        return $this->hasMany(ContaminationType::class, 'category_uuid', 'uuid')
            ->orderBy('sort_order');
    }
}