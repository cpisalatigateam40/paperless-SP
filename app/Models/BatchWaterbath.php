<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BatchWaterbath extends Model
{
    use HasFactory;

    protected $table = 'batch_waterbaths';
    protected $fillable = ['uuid', 'report_uuid', 'batch_code'];

    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }
}