<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportMdRejectVerification extends Model
{
    protected $table = 'report_md_reject_verifications';

    protected $fillable = ['uuid', 'report_uuid', 'test_piece', 'result', 'notes'];

    protected static function booted()
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportMdReject::class, 'report_uuid', 'uuid');
    }
}