<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class StorageFile extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'disk_name',
        'path',
        'extension',
        'size_in_kb',
        'file_name',
        'original_name',
        'public',
        'uploaded_by',
        'reference_class',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'public' => 'boolean',
    ];

    public static function getMimeTypeByExtension(string $file, bool $nullForOctetStream = true): ?string
    {
        $mime = \Illuminate\Http\Testing\MimeType::from($file);

        return ($mime === 'application/octet-stream') && $nullForOctetStream ? null : $mime;
    }

    public function getMimeType()
    {
        return $this->extension ?: static::getMimeTypeByExtension($this->path);
    }

    public function getUrlAttribute()
    {
        return $this->disk_name ? Storage::disk($this->disk_name)->url($this->path) : null;
    }

    public function getFullPathAttribute()
    {
        return $this->disk_name ? Storage::disk($this->disk_name)->path($this->path) : null;
    }
}
