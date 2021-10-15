<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'file_size',
        'mime_type',
        'file_name'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function getFileSizeAttribute()
    {
        return Storage::disk('public')->size($this->file_path);
    }

    public function getMimeTypeAttribute()
    {
        return Storage::disk('public')->mimeType($this->file_path);
    }

    public function getFileNameAttribute()
    {
        return File::name(Storage::disk('public')->url($this->file_path));
    }
}
