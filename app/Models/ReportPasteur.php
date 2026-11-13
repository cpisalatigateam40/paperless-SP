<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportPasteur extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_pasteurs';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'problem',
        'corrective_action',
        'is_audit',
        'source_uuid',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'is_audit' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
        static::addGlobalScope(new UserAreaScope);
    }

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->loadMissing([
                'details.runs.steps.standardStep',
                'details.runs.steps.finishStep',
                'details.steps.standardStep',
                'details.steps.drainageStep',
                'details.steps.finishStep',
            ]);

            foreach ($this->details as $detail) {
                $newDetail = $detail->replicate();
                $newDetail->uuid        = (string) Str::uuid();
                $newDetail->report_uuid = $clone->uuid;
                $newDetail->save();

                // Format baru: run beserta step-nya
                foreach ($detail->runs as $run) {
                    $newRun = $run->replicate();
                    $newRun->uuid        = (string) Str::uuid();   // PasteurRun memakai ??=, jadi uuid wajib diset manual
                    $newRun->detail_uuid = $newDetail->uuid;
                    $newRun->save();

                    foreach ($run->steps as $step) {
                        $this->copyStepToAudit($step, $newDetail->uuid, $newRun->uuid);
                    }
                }

                // Format lama: step tanpa run
                foreach ($detail->steps->whereNull('run_uuid') as $step) {
                    $this->copyStepToAudit($step, $newDetail->uuid, null);
                }
            }

            return $clone;
        });
    }

    private function copyStepToAudit(StepPasteur $step, string $detailUuid, ?string $runUuid): void
    {
        $newStep = $step->replicate();
        $newStep->uuid        = (string) Str::uuid();
        $newStep->detail_uuid = $detailUuid;
        $newStep->run_uuid    = $runUuid;
        $newStep->save();

        foreach (['standardStep', 'drainageStep', 'finishStep'] as $rel) {
            if ($child = $step->$rel) {
                $copy = $child->replicate();
                $copy->uuid      = (string) Str::uuid();
                $copy->step_uuid = $newStep->uuid;
                $copy->save();
            }
        }
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(DetailPasteur::class, 'report_uuid', 'uuid');
    }
}