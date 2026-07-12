<?php

namespace App\Modules\Cms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $template
 * @property string $status
 * @property bool $is_homepage
 * @property Carbon|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $canonical_url
 * @property string $robots
 * @property-read Collection<int, PageSection> $sections
 * @property-read Collection<int, PageRevision> $revisions
 */
class Page extends Model
{
    use SoftDeletes;

    protected $table = 'module_cms_pages';

    /** @var list<string> */
    protected $fillable = [
        'created_by',
        'updated_by',
        'title',
        'slug',
        'excerpt',
        'template',
        'status',
        'is_homepage',
        'published_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            $reservedSlugs = array_values(array_filter(
                config('cms.reserved_slugs', []),
                'is_string',
            ));

            if (in_array($page->slug, $reservedSlugs, true)) {
                throw ValidationException::withMessages([
                    'slug' => 'This URL is reserved by the application and cannot be used for a CMS page.',
                ]);
            }

            if ($page->is_homepage) {
                $otherHomepages = static::query()->where('is_homepage', true);

                if ($page->exists) {
                    $otherHomepages->whereKeyNot($page->getKey());
                }

                $otherHomepages->update(['is_homepage' => false]);
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<PageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    /** @return HasMany<PageRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @param Builder<Page> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $publishedQuery): void {
                $publishedQuery
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** @return HasMany<PageSection, $this> */
    public function enabledSections(): HasMany
    {
        return $this->sections()->where('is_enabled', true);
    }

    public function createRevision(?int $userId = null): PageRevision
    {
        $this->loadMissing('sections');

        return $this->revisions()->create([
            'user_id' => $userId,
            'snapshot' => [
                'page' => $this->only([
                    'title',
                    'slug',
                    'excerpt',
                    'template',
                    'status',
                    'is_homepage',
                    'published_at',
                    'meta_title',
                    'meta_description',
                    'canonical_url',
                    'robots',
                ]),
                'sections' => $this->sections
                    ->map(fn (PageSection $section): array => $section->only([
                        'section_type',
                        'section_name',
                        'content',
                        'settings',
                        'sort_order',
                        'is_enabled',
                    ]))
                    ->values()
                    ->all(),
            ],
        ]);
    }
}
