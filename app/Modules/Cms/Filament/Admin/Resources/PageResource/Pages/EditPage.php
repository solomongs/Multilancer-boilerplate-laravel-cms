<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageResource;
use App\Modules\Cms\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @return array<int, Action|DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('cms.pages.preview', ['page' => $this->record->getKey()]))
                ->openUrlInNewTab(),
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
