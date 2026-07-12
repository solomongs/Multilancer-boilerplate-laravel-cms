<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource;
use App\Modules\Cms\Models\Page;
use Filament\Resources\Pages\CreateRecord;

class CreatePageSection extends CreateRecord
{
    protected static string $resource = PageSectionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pageId = $data['page_id'] ?? null;

        if (is_numeric($pageId)) {
            Page::query()->find((int) $pageId)?->createRevision(auth()->id());
        }

        return $data;
    }
}
