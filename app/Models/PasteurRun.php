<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class PasteurRun extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pasteur_runs';

    protected $fillable = [
        'uuid', 'detail_uuid', 'attempt', 'retort_chamber_number',
        'trolley_count', 'tray_count',
        'initial_temp', 'initial_temp_min', 'initial_temp_max',
        'sensor_ok', 'notes', 'start_time', 'end_time',
    ];

    protected $casts = [
        'sensor_ok' => 'boolean',
    ];

    protected $auditEvents = ['updated'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailPasteur::class, 'detail_uuid', 'uuid');
    }

    public function steps()
    {
        return $this->hasMany(StepPasteur::class, 'run_uuid', 'uuid')->orderBy('step_order');
    }

    public function isRepeat(): bool
    {
        return $this->attempt > 1;
    }

    public function getTotalMinutesAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) return null;

        $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $d = $toMin($this->end_time) - $toMin($this->start_time);

        return $d < 0 ? $d + 1440 : $d;   // lewat tengah malam
    }
}