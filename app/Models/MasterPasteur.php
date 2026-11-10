<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class MasterPasteur extends Model
{
    protected $fillable = ['uuid','area_uuid','product_uuid','initial_temp_min','initial_temp_max'];

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function product() { return $this->belongsTo(Product::class, 'product_uuid', 'uuid'); }

    public function steps()
    {
        return $this->hasMany(MasterPasteurStep::class, 'master_uuid', 'uuid')->orderBy('step_order');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // MasterPasteur
    public function getInitialTempLabelAttribute(): string
    {
        return MasterPasteurStep::rangeLabel($this->initial_temp_min, $this->initial_temp_max);
    }
}