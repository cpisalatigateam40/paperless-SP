<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SharpTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'name',
        'quantity',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailSharpTool::class, 'sharp_tool_uuid', 'uuid');
    }
}