<?php

namespace App\Modules\Cms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $page_id
 * @property int|null $user_id
 * @property array<string, mixed> $snapshot
 */
class PageRevision extends Model
{
    protected $table = 'module_cms_page_revisions';

    /** @var list<string> */
    protected $fillable = [
        'page_id',
        'user_id',
        'snapshot',
        'restored_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'restored_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restore(?int $restoredBy = null): Page
    {
        return DB::transaction(function () use ($restoredBy): Page {
            $page = $this->page()->lockForUpdate()->firstOrFail();
            $snapshot = $this->snapshot;
            $pageData = is_array($snapshot['page'] ?? null) ? $snapshot['page'] : [];
            $sections = is_array($snapshot['sections'] ?? null) ? $snapshot['sections'] : [];

            $page->createRevision($restoredBy);
            $page->fill($pageData);
            $page->updated_by = $restoredBy;
            $page->save();

            $page->sections()->delete();
            if ($sections !== []) {
                $page->sections()->createMany($sections);
            }

            $this->forceFill(['restored_at' => now()])->save();

            return $page->fresh(['sections']) ?? $page;
        });
    }
}
