<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\DocumentResource;
use App\Enums\DocumentStatus;
use Illuminate\Database\Query\Builder;

class DocumentCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'document_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'seo_title',
        'seo_description',
        'show_on_tab_filter',
        'order_on_tab_filter',
        'icon',
        'is_canonical',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_on_tab_filter' => 'boolean',
        'is_canonical' => 'boolean',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_category_id');
    }

    public static function tabList(bool|Closure $updateCache = false): Collection|\Illuminate\Support\Collection
    {
        $updateCache = boolval(is_a($updateCache, Closure::class) ? $updateCache() : $updateCache);

        $cacheKey = http_build_query([
            __METHOD__,
            Auth::user()?->id,
        ]);

        if ($updateCache) {
            cache()->forget($cacheKey);
        }

        $categoriesAlias = 'dc';
        $categoriesToSelect = [
            "{$categoriesAlias}.id",
            "{$categoriesAlias}.name",
            "{$categoriesAlias}.slug",
            "{$categoriesAlias}.icon",
            "{$categoriesAlias}.show_on_tab_filter",
            "{$categoriesAlias}.order_on_tab_filter",
        ];

        return cache()
            ->remember(
                $cacheKey,
                60,
                fn() => DB::table('documents')
                    ->select([
                        ...$categoriesToSelect,
                        'documents.document_category_id',
                        DB::raw('COUNT(*) as count'),
                    ])
                    ->where(function (Builder $query) {
                        if (DocumentResource::userCanManage()) {
                            return $query;
                        }

                        return $query->where('public', '!=', true)
                            ->where('release_date', '<', now())
                            ->where(function (Builder $query) {
                                return $query->whereNull('available_until')
                                    ->orWhere('available_until', '>', now());
                            })
                            ->where('status', DocumentStatus::PUBLISHED?->value);
                    })
                    ->where("{$categoriesAlias}.show_on_tab_filter", true)
                    ->leftJoin(
                        "document_categories as {$categoriesAlias}",
                        "{$categoriesAlias}.id",
                        '=',
                        'documents.document_category_id',
                    )
                    ->groupBy([
                        ...$categoriesToSelect,
                        'documents.document_category_id',
                    ])
                    ->havingRaw('COUNT(*) > 0')
                    ->get()
                    ->sortBy('order_on_tab_filter')
                    ->sortBy(
                        'count',
                        descending: true,
                    )
            );
    }
}
