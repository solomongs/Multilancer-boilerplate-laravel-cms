<?php

namespace App\Modules\Cms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $page_id
 * @property string $section_type
 * @property string|null $section_name
 * @property array<string, mixed>|null $content
 * @property array<string, mixed>|null $settings
 * @property int $sort_order
 * @property bool $is_enabled
 */
class PageSection extends Model
{
    protected $table = 'module_cms_page_sections';

    /** @var list<string> */
    protected $fillable = [
        'page_id',
        'section_type',
        'section_name',
        'content',
        'settings',
        'sort_order',
        'is_enabled',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @param Builder<PageSection> $query */
    public function scopeRenderable(Builder $query): Builder
    {
        return $query
            ->where('is_enabled', true)
            ->orderBy('sort_order');
    }
}
