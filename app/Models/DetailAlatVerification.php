<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailAlatVerification extends Model
{
    use HasFactory;

    protected $table = 'detail_alat_verifications';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'report_alat_verification_uuid',
        'alat_type',
        'alat_uuid',
        'titik_ukur',
        'nilai_baca',
        'check_time',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportAlatVerification::class, 'report_alat_verification_uuid', 'uuid');
    }

    public function alat()
    {
        return $this->morphTo(__FUNCTION__, 'alat_type', 'alat_uuid', 'uuid');
    }
}