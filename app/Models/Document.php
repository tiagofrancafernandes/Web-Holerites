<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\StorageFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'release_date',
        'available_until',
        'internal_note',
        'public_note',
        'storage_file_id',
        'created_by',
        'public',
        'document_category_id',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'release_date' => 'datetime',
        'available_until' => 'datetime',
        'public' => 'boolean',
    ];

    /**
     * Get the file associated with the Document
     *
     * @return ?StorageFile
     */

    public function getFileAttribute()
    {
        return $this->file()?->first();
    }

    /**
     * Get the file associated with the Document
     *
     * @return ?\Illuminate\Database\Eloquent\Builder
     */
    public function file()
    {
        return $this->storage_file_id ? StorageFile::where('id', $this->storage_file_id) : null;
    }

    /**
     * Get the creator associated with the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function canBeViewed(
        ?User $user = null,
    ): bool {
        if (
            $user &&
            (
                $this->created_by === $user?->id
                || $user?->can('Document:read-all')
            )
        ) {
            return true;
        }

        if (is_null($this->status) || !$this->storage_file_id) {
            return false;
        }

        if ($this->available_until && $this->available_until?->unix() < now()->unix()) {
            return false;
        }

        if ($this->release_date && $this->release_date?->unix() < now()->unix()) {
            return false;
        }

        return ($this->status == DocumentStatus::PUBLISHED) ? true : false;
    }
}
