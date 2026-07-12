<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\PageSection;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageSection extends EditRecord
{
    protected static string $resource = PageSectionResource::class;

    /** @return array<int, DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (): void {
                    $record = $this->record;

                    if ($record instanceof PageSection) {
                        $record->page?->createRevision(auth()->id());
                    }
                }),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->record;

        if ($record instanceof PageSection) {
            $record->page?->createRevision(auth()->id());
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;
        $newPageId = $data['page_id'] ?? null;

        if ($record instanceof PageSection && is_numeric($newPageId) && (int) $newPageId !== $record->page_id) {
            Page::query()->find((int) $newPageId)?->createRevision(auth()->id());
        }

        return $data;
    }
}
