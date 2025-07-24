<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HeaderEmulsionMaking extends Model
{
    use HasFactory;

    protected $table = 'header_emulsion_makings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'emulsion_type',
        'production_code',
    ];

    public function report()
    {
        return $this->belongsTo(ReportEmulsionMaking::class, 'report_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailEmulsionMaking::class, 'header_uuid', 'uuid');
    }

    public function agings()
    {
        return $this->hasMany(AgingEmulsionMaking::class, 'header_uuid', 'uuid');
    }
}