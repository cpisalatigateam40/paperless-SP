<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DetailStartupLabelPhoto extends Model
{
    use HasFactory;

    protected $table = 'detail_startup_label_photos';

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'file_path',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailStartupLabel::class, 'detail_uuid', 'uuid');
    }

    /**
     * Base64 data URI untuk ditampilkan di PDF (DomPDF gak bisa load via URL).
     */
    public function getBase64Attribute()
    {
        if (!Storage::disk('public')->exists($this->file_path)) {
            return null;
        }

        $content = Storage::disk('public')->get($this->file_path);
        $mime    = Storage::disk('public')->mimeType($this->file_path) ?? 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }
}