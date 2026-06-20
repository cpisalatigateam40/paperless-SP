<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KartoningDocumentation extends Model
{
    use SoftDeletes;

    protected $table = 'kartoning_documentations';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'image',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function detail()
    {
        return $this->belongsTo(
            DetailFreezPackaging::class,
            'detail_uuid',
            'uuid'
        );
    }
}