<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailRepairCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_repair_cleanliness';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'equipment_uuid',
        'section_uuid',
        'repair_type',
        'clean_condition',
        'spare_part_left',
        'notes',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportRepairCleanliness::class, 'report_uuid', 'uuid');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }
}