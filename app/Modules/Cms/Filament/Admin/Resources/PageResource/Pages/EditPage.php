<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageResource;
use App\Modules\Cms\Models\Page;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @return array<int, DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->record;

        if ($record instanceof Page && $record->exists) {
            $record->createRevision(auth()->id());
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if (($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
