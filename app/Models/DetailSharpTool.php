<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailSharpTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'sharp_tool_uuid',
        'qty_start',
        'qty_end',
        'check_time_1',
        'condition_1',
        'check_time_2',
        'condition_2',
        'note',
    ];

    public function report()
    {
        return $this->belongsTo(ReportSharpTool::class, 'report_uuid', 'uuid');
    }

    public function sharpTool()
    {
        return $this->belongsTo(SharpTool::class, 'sharp_tool_uuid', 'uuid');
    }
}