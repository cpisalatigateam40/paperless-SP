<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailProcessAreaCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_process_area_cleanliness';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'inspection_hour',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportProcessAreaCleanliness::class, 'report_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(ItemProcessAreaCleanliness::class, 'detail_uuid', 'uuid');
    }
}