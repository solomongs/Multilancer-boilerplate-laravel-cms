<?php

namespace App\Modules\Cms\Filament\Admin\Resources\RedirectResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\RedirectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRedirect extends EditRecord
{
    protected static string $resource = RedirectResource::class;

    /** @return array<int, DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
