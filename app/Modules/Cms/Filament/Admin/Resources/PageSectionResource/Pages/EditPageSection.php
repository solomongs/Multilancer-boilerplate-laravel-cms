<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageSection extends EditRecord
{
    protected static string $resource = PageSectionResource::class;

    /** @return array<int, DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
