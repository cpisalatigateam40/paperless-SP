<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProcessSensoric extends Model
{
    use HasFactory;

    protected $table = 'process_sensories';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'homogeneous',
        'stiffness',
        'aroma',
        'foreign_object'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailProcessProd::class, 'detail_uuid', 'uuid');
    }
}