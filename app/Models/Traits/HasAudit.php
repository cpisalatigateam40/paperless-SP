<?php
namespace App\Models\Traits;
use Illuminate\Support\Str;
trait HasAudit
{
    protected function keepApprovalsOnAudit(): bool
    {
        return false;
    }
    public function auditVersion()
    {
        return $this->hasOne(static::class, 'source_uuid', 'uuid')->where('is_audit', true);
    }
    public function originalVersion()
    {
        return $this->belongsTo(static::class, 'source_uuid', 'uuid')->where('is_audit', false);
    }
    public function copyToAudit(): self
    {
        $clone = $this->replicate();
        $clone->uuid = (string) Str::uuid();
        $clone->is_audit = true;
        $clone->source_uuid = $this->uuid;
        if (!$this->keepApprovalsOnAudit()) {
            $booleanApprovalFields = ['approved_by_qc', 'approved_by_produksi', 'approved_by_spv'];
            $nullableApprovalFields = [
                'qc_approved_by',
                'qc_approved_at',
                'produksi_approved_by',
                'produksi_approved_at',
                'spv_approved_by',
                'spv_approved_at',
            ];
            foreach ($booleanApprovalFields as $field) {
                if (array_key_exists($field, $clone->getAttributes())) {
                    $clone->{$field} = false;
                }
            }
            foreach ($nullableApprovalFields as $field) {
                if (array_key_exists($field, $clone->getAttributes())) {
                    $clone->{$field} = null;
                }
            }
        }
        $clone->save();
        return $clone;
    }
    protected function copyChildren(\Illuminate\Database\Eloquent\Model $oldParent, \Illuminate\Database\Eloquent\Model $newParent, array $tree): void
    {
        foreach ($tree as $relation => $subTree) {
            $rel = $oldParent->{$relation}();
            $fk = $rel->getForeignKeyName();
            $localKey = $rel->getLocalKeyName();

            foreach ($rel->withoutGlobalScopes()->get() as $row) {
                $copy = $row->replicate();
                $copy->{$fk} = $newParent->{$localKey};

                if (array_key_exists('uuid', $row->getAttributes())) {
                    $copy->uuid = (string) Str::uuid();
                }

                $copy->save();

                if (!empty($subTree)) {
                    $this->copyChildren($row, $copy, $subTree);
                }
            }
        }
    }
    public function scopeOperasional($query)
    {
        return $query->where('is_audit', false);
    }
    public function scopeAudit($query)
    {
        return $query->where('is_audit', true);
    }
}