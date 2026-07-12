<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPageSections extends ListRecords
{
    protected static string $resource = PageSectionResource::class;

    /** @return array<int, CreateAction> */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
