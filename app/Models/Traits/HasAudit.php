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

    protected function auditNormalizeRules(): array
    {
        return [];
    }

    public function hasAuditNormalizeRules(): bool
    {
        return !empty($this->auditNormalizeRules());
    }

    /**
     * Hitung perubahan untuk satu baris berdasarkan nilai aslinya (tidak menyimpan).
     */
    protected function auditRowUpdates($row, array $fields): array
    {
        $updates = [];

        foreach ($fields as $field => $rule) {
            $isBad = isset($rule['when'])
                ? (bool) $rule['when']($row)
                : in_array($row->{$field}, $rule['bad'] ?? [], true);

            if ($isBad) {
                $updates[$field] = $rule['ok'] instanceof \Closure ? ($rule['ok'])($row) : $rule['ok'];
            }
        }

        return $updates;
    }

    /**
     * true kalau masih ada nilai yang akan diubah (hanya membaca, tidak menyimpan).
     */
    public function wouldNormalizeAudit(): bool
    {
        foreach ($this->auditNormalizeRules() as $path => $fields) {
            foreach ($this->resolveAuditRows($this, explode('.', $path)) as $row) {
                if ($this->auditRowUpdates($row, $fields)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function normalizeAudit(): int
    {
        $count = 0;

        foreach ($this->auditNormalizeRules() as $path => $fields) {
            foreach ($this->resolveAuditRows($this, explode('.', $path)) as $row) {
                if ($updates = $this->auditRowUpdates($row, $fields)) {
                    foreach ($updates as $field => $value) {
                        $row->{$field} = $value;
                    }
                    $row->save();
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Ambil baris pada path relasi bersarang, mis. "header.details".
     */
    protected function resolveAuditRows(\Illuminate\Database\Eloquent\Model $model, array $segments)
    {
        $relation = array_shift($segments);
        $rows = $model->{$relation}()->withoutGlobalScopes()->get();

        if (empty($segments)) {
            return $rows;
        }

        return $rows->flatMap(fn ($row) => $this->resolveAuditRows($row, $segments));
    }
}